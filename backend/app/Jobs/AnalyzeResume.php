<?php

namespace App\Jobs;

use App\Exceptions\AiResponseException;
use App\Exceptions\AiTemporarilyUnavailableException;
use App\Models\Analysis;
use App\Models\AnalysisFeedback;
use App\Services\AnalysisResultNormalizer;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyzeResume implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    /** Seconds to wait before each retry. */
    public array $backoff = [15, 45];

    public function __construct(public Analysis $analysis)
    {
    }

    public function handle(OpenAIService $openai, AnalysisResultNormalizer $normalizer): void
    {
        if ($this->analysis->status === 'completed') {
            return;
        }

        $this->analysis->update(['status' => 'processing', 'error_message' => null]);

        if (empty(config('services.openai.api_key'))) {
            $this->fail('Resume analysis is not configured yet. Please try again later.');

            return;
        }

        $resume = $this->analysis->resume;

        if (!$resume?->isReadable() || trim((string) $resume->extracted_text) === '') {
            $this->fail('We could not read the text of this resume, so there is nothing to analyse.');

            return;
        }

        try {
            $response = $openai->analyze(
                $this->systemPrompt(),
                $this->buildUserMessage($resume)
            );
        } catch (AiTemporarilyUnavailableException $e) {
            // Leave the row processing and let the queue retry with backoff.
            Log::warning('Analysis retrying', [
                'analysis_id' => $this->analysis->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (AiResponseException $e) {
            $this->fail('The analysis came back in a form we could not read. Please try running it again.');

            return;
        }

        $normalized = $normalizer->normalize($response['content']);

        if (!$normalizer->isUsable($normalized)) {
            Log::error('Analysis produced no usable result', [
                'analysis_id' => $this->analysis->id,
                'raw' => $response['content'],
            ]);

            $this->fail('The analysis did not return any usable feedback. Please try running it again.');

            return;
        }

        $this->save($normalized, $response['content'], $response['tokens_used']);
    }

    public function failed(\Throwable $exception): void
    {
        $this->fail('The analysis service was unavailable. Please try running this analysis again.');

        Log::error('Analysis failed permanently', [
            'analysis_id' => $this->analysis->id,
            'error' => $exception->getMessage(),
        ]);
    }

    private function save(array $normalized, array $raw, int $tokensUsed): void
    {
        DB::transaction(function () use ($normalized, $raw, $tokensUsed) {
            // Retries must not stack duplicate feedback onto the same analysis.
            $this->analysis->feedback()->delete();

            foreach ($normalized['feedback'] as $item) {
                AnalysisFeedback::create([
                    'analysis_id' => $this->analysis->id,
                    'category' => $item['category'],
                    'severity' => $item['severity'],
                    'message' => $item['message'],
                    'suggestion' => $item['suggestion'],
                    'section' => $item['section'],
                ]);
            }

            $this->analysis->update([
                'status' => 'completed',
                'ats_score' => $normalized['score'],
                'result' => $normalized,
                'raw_response' => $raw,
                'tokens_used' => $tokensUsed,
                'error_message' => null,
                'completed_at' => now(),
            ]);
        });
    }

    private function fail(string $reason): void
    {
        $this->analysis->update([
            'status' => 'failed',
            'error_message' => $reason,
        ]);
    }

    private function systemPrompt(): string
    {
        return config(
            "prompts.types.{$this->analysis->type}.system",
            config('prompts.types.ats.system', '')
        );
    }

    private function buildUserMessage($resume): string
    {
        $userPrompt = config(
            "prompts.types.{$this->analysis->type}.user",
            config('prompts.types.ats.user', '')
        );

        $message = "{$userPrompt}\n\nResume Content:\n{$resume->extracted_text}";

        if ($this->analysis->type === 'comparison' && $this->analysis->job_description) {
            $message .= "\n\nJob Description:\n{$this->analysis->job_description}";
        }

        return $message;
    }
}
