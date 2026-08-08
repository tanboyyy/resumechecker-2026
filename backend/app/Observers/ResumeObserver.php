<?php

namespace App\Observers;

use App\Events\ResumeUploaded;
use App\Models\Resume;

class ResumeObserver
{
    public function created(Resume $resume): void
    {
        ResumeUploaded::dispatch($resume);
    }
}
