<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

class ExtractResumeText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public Resume $resume)
    {
    }

    public function handle(): void
    {
        $text = $this->extractTextFromDisk();

        if (empty(trim($text))) {
            $text = $this->extractWithOpenAI($text);
        }

        $this->resume->update([
            'text_extracted' => true,
            'extracted_text' => $text,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->resume->update([
            'text_extracted' => false,
            'extracted_text' => null,
        ]);

        \Illuminate\Support\Facades\Log::error('Text extraction failed', [
            'resume_id' => $this->resume->id,
            'error' => $exception->getMessage(),
        ]);
    }

    private function extractTextFromDisk(): string
    {
        $contents = Storage::disk($this->resume->disk)->get($this->resume->storage_path);

        if ($contents === null) {
            return '';
        }

        $mimeType = $this->resume->mime_type;

        return match (true) {
            str_contains($mimeType, 'pdf') => $this->extractPdf($contents),
            str_contains($mimeType, 'word') => $this->extractWord($contents),
            str_contains($mimeType, 'text') => $contents,
            default => '',
        };
    }

    private function extractPdf(string $contents): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'resume_');
        file_put_contents($tempFile, $contents);

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($tempFile);

            return $pdf->getText();
        } finally {
            @unlink($tempFile);
        }
    }

    private function extractWord(string $contents): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'resume_') . '.docx';
        file_put_contents($tempFile, $contents);

        try {
            $phpWord = IOFactory::load($tempFile);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            return $text;
        } finally {
            @unlink($tempFile);
        }
    }

    private function extractWithOpenAI(string $currentText): string
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            return $currentText;
        }

        $openai = new OpenAIService();

        return $openai->extractText($currentText);
    }
}
