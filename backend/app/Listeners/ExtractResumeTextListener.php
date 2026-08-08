<?php

namespace App\Listeners;

use App\Jobs\ExtractResumeText;
use App\Events\ResumeUploaded;

class ExtractResumeTextListener
{
    public function handle(ResumeUploaded $event): void
    {
        ExtractResumeText::dispatch($event->resume);
    }
}
