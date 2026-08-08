<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckAiStatus extends Command
{
    protected $signature = 'ai:status';

    protected $description = 'Check that the analysis provider is reachable and the key works';

    public function handle(): int
    {
        $apiKey = config('services.openai.api_key');
        $baseUrl = rtrim((string) config('services.openai.base_url'), '/');
        $model = config('services.openai.model');

        if (empty($apiKey)) {
            $this->error('No API key configured. Set OPENAI_API_KEY.');

            return self::FAILURE;
        }

        $this->line("Endpoint: {$baseUrl}");
        $this->line("Model:    {$model}");

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => 'Reply with the single word: ok']],
                'max_tokens' => 5,
            ]);
        } catch (\Throwable $e) {
            $this->error("Could not reach the provider: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($response->failed()) {
            $this->error("HTTP {$response->status()}: " . mb_substr($response->body(), 0, 300));

            return self::FAILURE;
        }

        $this->info('Connected. Provider answered successfully.');

        foreach (['x-ratelimit-remaining-requests', 'ratelimit-remaining'] as $header) {
            if ($remaining = $response->header($header)) {
                $this->line("Requests remaining: {$remaining}");
                break;
            }
        }

        return self::SUCCESS;
    }
}
