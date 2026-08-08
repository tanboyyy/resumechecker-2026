<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_id',
        'stripe_status',
        'stripe_price_id',
        'plan',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function active(): bool
    {
        return $this->stripe_status === 'active'
            && !$this->ended()
            && !$this->onGracePeriod();
    }

    public function onGracePeriod(): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->isFuture();
    }

    public function ended(): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->isPast();
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function getPlanLimits(): array
    {
        return config("plans.plans.{$this->plan}.limits", []);
    }

    public function getAnalysisLimit(): int
    {
        return $this->getPlanLimits()['analyses_per_month'] ?? 3;
    }

    public function getMaxFileSize(): int
    {
        return $this->getPlanLimits()['max_resume_size'] ?? 5 * 1024 * 1024;
    }

    public function canExportReports(): bool
    {
        return $this->getPlanLimits()['export_reports'] ?? false;
    }

    public function hasPriorityProcessing(): bool
    {
        return $this->getPlanLimits()['priority_processing'] ?? false;
    }
}
