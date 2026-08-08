<?php

use App\Models\Analysis;
use App\Services\AnalysisResultNormalizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $normalizer = new AnalysisResultNormalizer();

        // Completed analyses predate the normalisation step. Recover what the
        // model actually returned so existing results are not blank in the UI.
        Analysis::query()
            ->where('status', 'completed')
            ->whereNull('result')
            ->whereNotNull('raw_response')
            ->each(function (Analysis $analysis) use ($normalizer) {
                $normalized = $normalizer->normalize($analysis->raw_response);

                $analysis->update([
                    'result' => $normalized,
                    'ats_score' => $analysis->ats_score ?? $normalized['score'],
                ]);
            });

        // Errors from an earlier attempt were never cleared on success.
        Analysis::query()
            ->where('status', 'completed')
            ->whereNotNull('error_message')
            ->update(['error_message' => null]);
    }

    public function down(): void
    {
        // Nothing to undo: this only recovers data that was already stored.
    }
};
