<?php

namespace App\Services;

use App\Exceptions\AiResponseException;
use App\Exceptions\AiTemporarilyUnavailableException;
use Illuminate\Http\Client\ConnectionException;
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
        $this->baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = config('services.openai.model', 'gpt-4o');
        $this->temperature = (float) config('services.openai.temperature', 0.3);
        $this->maxTokens = (int) config('services.openai.max_tokens', 4000);
    }

    /**
     * @return array{content: array, tokens_used: int}
     *
     * @throws AiTemporarilyUnavailableException when retrying is worthwhile
     * @throws AiResponseException when it is not
     */
    public function analyze(string $systemPrompt, string $userMessage): array
    {
        try {
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
                // Ask the provider to guarantee parseable JSON. Providers that do
                // not honour this simply ignore it, which is why the response is
                // still repaired and normalised downstream.
                'response_format' => ['type' => 'json_object'],
            ]);
        } catch (ConnectionException $e) {
            throw new AiTemporarilyUnavailableException('Could not reach the analysis service.', previous: $e);
        }

        if ($response->failed()) {
            $this->throwForStatus($response->status(), $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = (int) ($data['usage']['total_tokens'] ?? 0);

        if (trim($content) === '') {
            throw new AiResponseException('The analysis service returned an empty response.');
        }

        return [
            'content' => $this->decode($content),
            'tokens_used' => $tokensUsed,
        ];
    }

    private function throwForStatus(int $status, string $body): never
    {
        Log::error('AI request failed', ['status' => $status, 'body' => mb_substr($body, 0, 500)]);

        // 429 and 5xx are worth another attempt; 4xx means the request itself
        // is wrong (bad key, bad model, malformed payload) and will not fix itself.
        if ($status === 429 || $status >= 500) {
            throw new AiTemporarilyUnavailableException("The analysis service is busy (HTTP {$status}).");
        }

        throw new AiResponseException("The analysis service rejected the request (HTTP {$status}).");
    }

    /**
     * Parse the model's reply, repairing the malformations that show up in practice.
     */
    private function decode(string $content): array
    {
        foreach ($this->repairCandidates($content) as $candidate) {
            $decoded = json_decode($candidate, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        Log::error('Could not parse AI response as JSON', ['content' => mb_substr($content, 0, 1000)]);

        throw new AiResponseException('The analysis service returned a response we could not read.');
    }

    /**
     * Progressively more aggressive repairs, cheapest first.
     *
     * @return \Generator<string>
     */
    private function repairCandidates(string $content): \Generator
    {
        $content = trim($content);

        yield $content;

        // ```json ... ``` fences.
        if (str_starts_with($content, '```')) {
            $content = trim(preg_replace('/^```(?:json)?\s*/i', '', $content));
            $content = trim(preg_replace('/\s*```$/', '', $content));

            yield $content;
        }

        // Prose before or after the object.
        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $content = substr($content, $start, $end - $start + 1);

            yield $content;
        }

        // Trailing commas before a closing brace or bracket.
        $withoutTrailingCommas = preg_replace('/,\s*([}\]])/', '$1', $content);

        yield $withoutTrailingCommas;

        // Literal newlines and tabs inside string values, which JSON forbids.
        // Escaped rather than deleted, so words are not run together.
        yield $this->escapeControlCharsInStrings($withoutTrailingCommas);
    }

    private function escapeControlCharsInStrings(string $json): string
    {
        $out = '';
        $inString = false;
        $escaped = false;

        foreach (str_split($json) as $char) {
            if ($escaped) {
                $out .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\' && $inString) {
                $out .= $char;
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                $out .= $char;
                continue;
            }

            if ($inString && ord($char) < 0x20) {
                $out .= match ($char) {
                    "\n" => '\\n',
                    "\r" => '\\r',
                    "\t" => '\\t',
                    default => ' ',
                };
                continue;
            }

            $out .= $char;
        }

        return $out;
    }
}
