<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;

class BillingController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function plans(): JsonResponse
    {
        $plans = config('plans.plans');

        return response()->json([
            'plans' => collect($plans)->map(fn ($plan, $key) => [
                'id' => $key,
                'name' => $plan['name'],
                'price' => $plan['price'],
                'price_display' => $plan['price'] === 0 ? 'Free' : '$' . number_format($plan['price'] / 100, 2) . '/mo',
                'features' => $plan['features'],
            ])->values(),
        ]);
    }

    public function subscription(Request $request): JsonResponse
    {
        $subscription = $request->user()->subscription;

        if (!$subscription) {
            return response()->json([
                'plan' => 'free',
                'status' => 'active',
                'limits' => config('plans.plans.free.limits'),
            ]);
        }

        return response()->json([
            'plan' => $subscription->plan,
            'status' => $subscription->stripe_status,
            'ends_at' => $subscription->ends_at,
            'limits' => $subscription->getPlanLimits(),
        ]);
    }

    public function usage(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        $analysesThisMonth = $user->analyses()
            ->whereIn('analyses.status', ['pending', 'processing', 'completed'])
            ->whereMonth('analyses.created_at', now()->month)
            ->whereYear('analyses.created_at', now()->year)
            ->count();

        $analysisLimit = $subscription?->getAnalysisLimit()
            ?? config('plans.plans.free.limits.analyses_per_month');

        return response()->json([
            'analyses_used' => $analysesThisMonth,
            'analysis_limit' => $analysisLimit,
            'resumes_count' => $user->resumes()->count(),
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:pro,enterprise',
        ]);

        $user = $request->user();
        $plan = config("plans.plans.{$validated['plan']}");
        $priceId = $plan['stripe_price_id'];

        if (!$priceId) {
            return response()->json(['message' => 'Plan not available'], 400);
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'customer' => $this->getOrCreateStripeCustomer($user),
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.frontend_url') . '/dashboard?upgraded=true',
                'cancel_url' => config('app.frontend_url') . '/dashboard?cancelled=true',
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $validated['plan'],
                ],
            ]);

            return response()->json(['url' => $session->url]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create checkout session'], 500);
        }
    }

    public function portal(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->stripe_id) {
            return response()->json(['message' => 'No active subscription'], 400);
        }

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $this->getOrCreateStripeCustomer($user),
                'return_url' => config('app.frontend_url') . '/dashboard',
            ]);

            return response()->json(['url' => $session->url]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe portal failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create portal session'], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.created',
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function getOrCreateStripeCustomer($user): string
    {
        $subscription = $user->subscription;

        if ($subscription && $subscription->stripe_id) {
            return $subscription->stripe_id;
        }

        $customer = \Stripe\Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        return $customer->id;
    }

    private function handleCheckoutCompleted($session): void
    {
        $userId = $session->metadata->user_id ?? null;
        $plan = $session->metadata->plan ?? null;

        if (!$userId || !$plan) {
            return;
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return;
        }

        $stripeSubscription = StripeSubscription::retrieve($session->subscription);

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'stripe_id' => $stripeSubscription->id,
                'stripe_status' => $stripeSubscription->status,
                'stripe_price_id' => $stripeSubscription->items->data[0]->price->id ?? null,
                'plan' => $plan,
                'quantity' => $stripeSubscription->items->data[0]->quantity ?? 1,
                'trial_ends_at' => $stripeSubscription->trial_end
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end)
                    : null,
            ]
        );
    }

    private function handleSubscriptionUpdated($subscription): void
    {
        $local = Subscription::where('stripe_id', $subscription->id)->first();

        if ($local) {
            $local->update([
                'stripe_status' => $subscription->status,
                'stripe_price_id' => $subscription->items->data[0]->price->id ?? null,
                'quantity' => $subscription->items->data[0]->quantity ?? 1,
                'ends_at' => $subscription->ended_at
                    ? \Carbon\Carbon::createFromTimestamp($subscription->ended_at)
                    : null,
            ]);
        }
    }

    private function handleSubscriptionDeleted($subscription): void
    {
        $local = Subscription::where('stripe_id', $subscription->id)->first();

        if ($local) {
            $local->update([
                'stripe_status' => 'canceled',
                'ends_at' => \Carbon\Carbon::now(),
            ]);
        }
    }

    private function handlePaymentFailed($invoice): void
    {
        Log::warning('Stripe payment failed', [
            'customer' => $invoice->customer,
            'amount' => $invoice->amount_due,
        ]);
    }
}
