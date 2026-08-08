<?php

namespace App\Jobs;

use App\Models\Analysis;
use App\Models\AnalysisFeedback;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeResume implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 180;

    public function __construct(public Analysis $analysis)
    {
    }

    public function handle(): void
    {
        $this->analysis->update(['status' => 'processing']);

        try {
            $apiKey = config('services.openai.api_key');

            if (empty($apiKey)) {
                $this->analysis->update([
                    'status' => 'completed',
                    'ats_score' => 0,
                    'raw_response' => ['summary' => 'OpenAI not configured'],
                    'completed_at' => now(),
                ]);
                return;
            }

            $resume = $this->analysis->resume;
            $type = $this->analysis->type;

            $systemPrompt = config("prompts.types.{$type}.system", config('prompts.types.ats.system', ''));
            $userPrompt = config("prompts.types.{$type}.user", config('prompts.types.ats.user', ''));
            $userMessage = $this->buildUserMessage($resume, $type, $userPrompt);

            $openai = new OpenAIService();
            $result = $openai->analyze($systemPrompt, $userMessage);

            $this->saveFeedback($result['content']);
            $this->saveRawResponse($result['content']);

            $this->analysis->update([
                'status' => 'completed',
                'tokens_used' => $result['tokens_used'],
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function buildUserMessage($resume, string $type, string $userPrompt): string
    {
        $extractedText = $resume->extracted_text ?? '';

        $message = "{$userPrompt}\n\nResume Content:\n{$extractedText}";

        if ($type === 'comparison' && $this->analysis->job_description) {
            $message .= "\n\nJob Description:\n{$this->analysis->job_description}";
        }

        return $message;
    }

    private function saveFeedback(array $content): void
    {
        $feedbackItems = $content['feedback'] ?? [];

        foreach ($feedbackItems as $item) {
            AnalysisFeedback::create([
                'analysis_id' => $this->analysis->id,
                'category' => $item['category'] ?? 'general',
                'severity' => $item['severity'] ?? 'info',
                'message' => $item['message'] ?? '',
                'suggestion' => $item['suggestion'] ?? null,
                'section' => $item['section'] ?? null,
            ]);
        }
    }

    private function saveRawResponse(array $content): void
    {
        $this->analysis->update([
            'raw_response' => $content,
            'ats_score' => $content['ats_score'] ?? $content['score'] ?? null,
        ]);
    }
}
