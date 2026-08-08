<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiController extends Controller
{
    public function status(): JsonResponse
    {
        $apiKey = config('services.openai.api_key');
        $baseUrl = config('services.openai.base_url');
        $model = config('services.openai.model');

        if (empty($apiKey)) {
            return response()->json([
                'connected' => false,
                'error' => 'No API key configured',
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'hi'],
                ],
                'max_tokens' => 5,
            ]);

            $rateLimit = $response->header('x-ratelimit-limit-requests') ?? $response->header('ratelimit-limit');
            $rateRemaining = $response->header('x-ratelimit-remaining-requests') ?? $response->header('ratelimit-remaining');
            $rateReset = $response->header('x-ratelimit-reset-requests') ?? $response->header('ratelimit-reset');

            return response()->json([
                'connected' => $response->successful(),
                'model' => $model,
                'rate_limit' => $rateLimit ? (int) $rateLimit : null,
                'rate_remaining' => $rateRemaining ? (int) $rateRemaining : null,
                'rate_reset' => $rateReset,
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini status check failed', ['error' => $e->getMessage()]);

            return response()->json([
                'connected' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
