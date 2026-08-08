<?php

namespace App\Services;

use App\Models\Analysis;
use App\Models\Resume;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generatePdf(Resume $resume, Analysis $analysis): string
    {
        $data = [
            'resume' => $resume,
            'analysis' => $analysis,
            'feedback' => $analysis->feedback,
            'score' => $analysis->ats_score,
            'generated_at' => now()->format('M d, Y \a\t g:i A'),
        ];

        $pdf = Pdf::loadView('reports.analysis', $data);
        $pdf->setPaper('a4');

        $filename = "reports/{$resume->id}/{$analysis->id}.pdf";
        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }

    public function getTemporaryUrl(string $path, int $expiration = 300): ?string
    {
        return Storage::disk('local')->temporaryUrl($path, now()->addSeconds($expiration));
    }
}
