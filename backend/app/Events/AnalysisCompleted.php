<?php

namespace App\Events;

use App\Models\Analysis;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalysisCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Analysis $analysis)
    {
    }
}
