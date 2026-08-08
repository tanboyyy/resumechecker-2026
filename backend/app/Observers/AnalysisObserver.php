<?php

namespace App\Observers;

use App\Events\AnalysisCompleted;
use App\Models\Analysis;

class AnalysisObserver
{
    public function updated(Analysis $analysis): void
    {
        if ($analysis->wasChanged('status') && $analysis->status === 'completed') {
            AnalysisCompleted::dispatch($analysis);
        }
    }
}
