<?php

namespace App\Jobs;

use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class ExtractResumeText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Below this many characters a resume is not worth analysing — the model
     * would invent feedback about a document it effectively never read.
     */
    private const MINIMUM_USEFUL_LENGTH = 100;

    public function __construct(public Resume $resume)
    {
    }

    public function handle(): void
    {
        try {
            $text = $this->extractTextFromDisk();
        } catch (UnreadableResumeException $e) {
            $this->markFailed($e->getMessage());

            return;
        } catch (\Throwable $e) {
            // Storage and other infrastructure faults are worth retrying.
            Log::error('Resume text extraction errored', [
                'resume_id' => $this->resume->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $text = trim($text);

        if (mb_strlen($text) < self::MINIMUM_USEFUL_LENGTH) {
            $this->markFailed(
                'We could not read any text from this file. If it is a scanned or image-based '
                . 'PDF, export a text version from your editor and upload that instead.'
            );

            return;
        }

        $this->resume->update([
            'text_extracted' => true,
            'extraction_status' => 'completed',
            'extraction_error' => null,
            'extracted_text' => $text,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->markFailed('Something went wrong while reading this file. Please try uploading it again.');

        Log::error('Resume text extraction failed permanently', [
            'resume_id' => $this->resume->id,
            'error' => $exception->getMessage(),
        ]);
    }

    private function markFailed(string $reason): void
    {
        $this->resume->update([
            'text_extracted' => false,
            'extraction_status' => 'failed',
            'extraction_error' => $reason,
            'extracted_text' => null,
        ]);
    }

    private function extractTextFromDisk(): string
    {
        $contents = Storage::disk($this->resume->disk)->get($this->resume->storage_path);

        if ($contents === null || $contents === '') {
            throw new UnreadableResumeException('This file appears to be empty. Please upload a different file.');
        }

        $mimeType = $this->resume->mime_type ?? '';

        return match (true) {
            str_contains($mimeType, 'pdf') => $this->extractPdf($contents),
            str_contains($mimeType, 'word'),
            str_contains($mimeType, 'officedocument') => $this->extractWord($contents),
            str_contains($mimeType, 'text') => $contents,
            default => throw new UnreadableResumeException(
                'We can only read PDF and DOCX files. Please upload one of those formats.'
            ),
        };
    }

    private function extractPdf(string $contents): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'resume_');
        file_put_contents($tempFile, $contents);

        try {
            return (new Parser())->parseFile($tempFile)->getText();
        } catch (\Throwable $e) {
            throw new UnreadableResumeException(
                'We could not read this PDF. It may be password protected, corrupted, or a scanned image. '
                . 'Try exporting a fresh PDF from your document editor.',
                previous: $e
            );
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
        } catch (\Throwable $e) {
            throw new UnreadableResumeException(
                'We could not read this Word document. Try re-saving it as .docx, or export it as a PDF.',
                previous: $e
            );
        } finally {
            @unlink($tempFile);
        }
    }
}
