<?php

namespace App\Listeners;

use App\Events\AnalysisCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendAnalysisNotification implements ShouldQueue
{
    public function handle(AnalysisCompleted $event): void
    {
        Log::info('Analysis completed', [
            'analysis_id' => $event->analysis->id,
            'type' => $event->analysis->type,
            'user_id' => $event->analysis->resume->user_id,
        ]);
    }
}
