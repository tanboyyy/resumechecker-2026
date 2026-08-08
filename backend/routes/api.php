<?php

use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\GeminiController;
use App\Http\Controllers\Api\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/csrf', [AuthController::class, 'csrf'])
    ->name('auth.csrf');

// Rendered inside an <iframe> on the frontend origin, where the session cookie
// is not sent (SameSite=Lax). Authorised by a short-lived signature instead,
// minted by the authenticated previewUrl endpoint below.
Route::get('/resumes/{resume}/preview', [ResumeController::class, 'preview'])
    ->middleware('signed')
    ->name('resumes.preview');

Route::get('/auth/google', [AuthController::class, 'redirect'])
    ->name('auth.google');

Route::get('/auth/google/callback', [AuthController::class, 'callback'])
    ->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user'])
        ->name('auth.user');

    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/gemini/status', [GeminiController::class, 'status'])
        ->name('gemini.status');

    Route::get('/resumes', [ResumeController::class, 'index'])
        ->name('resumes.index');

    Route::post('/resumes', [ResumeController::class, 'store'])
        ->middleware('plan:upload_resume')
        ->name('resumes.store');

    Route::get('/resumes/{resume}', [ResumeController::class, 'show'])
        ->name('resumes.show');

    Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])
        ->name('resumes.destroy');

    Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])
        ->name('resumes.download');

    Route::get('/resumes/{resume}/view', [ResumeController::class, 'viewPdf'])
        ->name('resumes.view');

    Route::get('/resumes/{resume}/preview-url', [ResumeController::class, 'previewUrl'])
        ->name('resumes.preview-url');

    Route::get('/resumes/{resume}/analyses', [AnalysisController::class, 'index'])
        ->name('analyses.index');

    Route::post('/resumes/{resume}/analyses', [AnalysisController::class, 'store'])
        ->middleware('plan:create_analysis')
        ->name('analyses.store');

    Route::get('/resumes/{resume}/analyses/{analysis}', [AnalysisController::class, 'show'])
        ->name('analyses.show');

    Route::delete('/resumes/{resume}/analyses/{analysis}', [AnalysisController::class, 'destroy'])
        ->name('analyses.destroy');

    Route::get('/resumes/{resume}/analyses/{analysis}/export', [AnalysisController::class, 'export'])
        ->middleware('plan:export_report')
        ->name('analyses.export');

    Route::get('/resumes/{resume}/analyses/{analysis}/status', [AnalysisController::class, 'status'])
        ->name('analyses.status');

    Route::get('/billing/plans', [BillingController::class, 'plans'])
        ->name('billing.plans');

    Route::get('/billing/subscription', [BillingController::class, 'subscription'])
        ->name('billing.subscription');

    Route::get('/billing/usage', [BillingController::class, 'usage'])
        ->name('billing.usage');

    Route::post('/billing/checkout', [BillingController::class, 'checkout'])
        ->name('billing.checkout');

    Route::post('/billing/portal', [BillingController::class, 'portal'])
        ->name('billing.portal');
});

Route::post('/billing/webhook', [BillingController::class, 'webhook'])
    ->name('billing.webhook')
    ->withoutMiddleware(['auth']);
