<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'resume_id',
        'type',
        'status',
        'ats_score',
        'raw_response',
        'result',
        'job_description',
        'prompt_used',
        'tokens_used',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'ats_score' => 'integer',
            'raw_response' => 'array',
            'result' => 'array',
            'tokens_used' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(AnalysisFeedback::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
