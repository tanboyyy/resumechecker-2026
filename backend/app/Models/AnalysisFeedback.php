<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisFeedback extends Model
{
    use HasFactory;

    protected $table = 'analysis_feedback';

    protected $fillable = [
        'analysis_id',
        'category',
        'severity',
        'message',
        'suggestion',
        'section',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
