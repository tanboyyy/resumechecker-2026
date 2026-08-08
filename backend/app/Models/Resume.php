<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'original_filename',
        'storage_path',
        'disk',
        'mime_type',
        'size',
        'extracted_text',
        'text_extracted',
        'extraction_status',
        'extraction_error',
    ];

    protected $hidden = [
        'storage_path',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'text_extracted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isReadable(): bool
    {
        return $this->extraction_status === 'completed';
    }

    public function extractionFailed(): bool
    {
        return $this->extraction_status === 'failed';
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public function getTemporaryUrl(int $expiration = 3600): ?string
    {
        if ($this->disk === 'local') {
            return Storage::disk('local')->url($this->storage_path);
        }

        return Storage::disk('s3')->temporaryUrl(
            $this->storage_path,
            now()->addSeconds($expiration)
        );
    }

    public function getFileContents(): ?string
    {
        return Storage::disk($this->disk)->get($this->storage_path);
    }

    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->storage_path);
    }

    public function getFileSizeHuman(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
