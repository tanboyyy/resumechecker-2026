<?php

namespace App\Providers;

use App\Models\Analysis;
use App\Models\Resume;
use App\Observers\AnalysisObserver;
use App\Observers\ResumeObserver;
use App\Policies\ResumePolicy;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\ResumeUploaded::class => [
            \App\Listeners\ExtractResumeTextListener::class,
        ],
        \App\Events\AnalysisCompleted::class => [
            \App\Listeners\SendAnalysisNotification::class,
        ],
    ];

    public function boot(): void
    {
        Resume::observe(ResumeObserver::class);
        Analysis::observe(AnalysisObserver::class);

        Gate::policy(Resume::class, ResumePolicy::class);
    }
}
