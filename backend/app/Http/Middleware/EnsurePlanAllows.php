<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanAllows
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $subscription = $user->subscription ?? $this->createDefaultSubscription($user);
        $limits = $subscription->getPlanLimits();

        return match ($feature) {
            'create_analysis' => $this->ensureAnalysisLimit($request, $subscription, $limits, $next),
            'upload_resume' => $this->ensureFileSizeLimit($request, $subscription, $limits, $next),
            'export_report' => $this->ensureFeatureAllowed($limits, 'export_reports', $next),
            default => $next($request),
        };
    }

    private function createDefaultSubscription($user): Subscription
    {
        return Subscription::make([
            'plan' => 'free',
            'stripe_status' => 'active',
        ]);
    }

    private function ensureAnalysisLimit(Request $request, Subscription $subscription, array $limits, Closure $next): Response
    {
        $limit = $limits['analyses_per_month'] ?? 3;

        if ($limit === -1) {
            return $next($request);
        }

        // Failed analyses are not charged for: an outage on our side should not
        // cost the user one of their monthly runs.
        $used = $request->user()->analyses()
            ->whereIn('analyses.status', ['pending', 'processing', 'completed'])
            ->whereMonth('analyses.created_at', now()->month)
            ->whereYear('analyses.created_at', now()->year)
            ->count();

        if ($used >= $limit) {
            return response()->json([
                'message' => 'Analysis limit reached for your current plan',
                'limit' => $limit,
                'used' => $used,
                'plan' => $subscription->plan,
                'upgrade_url' => '/billing',
            ], 403);
        }

        return $next($request);
    }

    private function ensureFileSizeLimit(Request $request, Subscription $subscription, array $limits, Closure $next): Response
    {
        $maxSize = $limits['max_resume_size'] ?? 5 * 1024 * 1024;
        $file = $request->file('file');

        if ($file && $file->getSize() > $maxSize) {
            return response()->json([
                'message' => 'File size exceeds your plan limit',
                'max_size' => $maxSize,
                'file_size' => $file->getSize(),
                'plan' => $subscription->plan,
                'upgrade_url' => '/billing',
            ], 403);
        }

        return $next($request);
    }

    private function ensureFeatureAllowed(array $limits, string $feature, Closure $next): Response
    {
        if (empty($limits[$feature])) {
            return response()->json([
                'message' => 'This feature is not available on your current plan',
                'feature' => $feature,
                'upgrade_url' => '/billing',
            ], 403);
        }

        return $next($request);
    }
}
