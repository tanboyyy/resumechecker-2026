<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private float $temperature;
    private int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
        $this->model = config('services.openai.model', 'gpt-4o');
        $this->temperature = config('services.openai.temperature', 0.3);
        $this->maxTokens = config('services.openai.max_tokens', 4000);
    }

    public function analyze(string $systemPrompt, string $userMessage): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('OpenAI API request failed: ' . $response->body());
        }

        $data = $response->json();

        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? 0;

        $content = $this->cleanJsonContent($content);

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse OpenAI response as JSON', [
                'content' => $content,
                'error' => json_last_error_msg(),
            ]);

            throw new \RuntimeException('Invalid JSON response from OpenAI');
        }

        return [
            'content' => $decoded,
            'tokens_used' => $tokensUsed,
        ];
    }

    public function extractText(string $content): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($this->baseUrl . '/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Extract the full text content from this resume document. Preserve structure and formatting. Return only the extracted text.',
                ],
                [
                    'role' => 'user',
                    'content' => "Extract the text from this resume:\n\n{$content}",
                ],
            ],
            'temperature' => 0.1,
            'max_tokens' => 4000,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI text extraction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('OpenAI text extraction failed');
        }

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    private function cleanJsonContent(string $content): string
    {
        $content = trim($content);

        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
        }

        // Strip all control characters including newlines and tabs
        $content = preg_replace('/[\x00-\x1F]/', '', $content);

        // If still broken, try to repair common issues
        if (json_decode($content) === null) {
            // Some models wrap JSON in extra quotes or add trailing commas
            $content = preg_replace('/,\s*([}\]])/', '$1', $content);
        }

        return $content;
    }
}
