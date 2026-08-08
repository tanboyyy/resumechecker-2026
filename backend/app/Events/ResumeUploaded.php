<?php

namespace App\Events;

use App\Models\Resume;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResumeUploaded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Resume $resume)
    {
    }
}
