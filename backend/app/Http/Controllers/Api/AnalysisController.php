<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analysis\CreateAnalysisRequest;
use App\Http\Resources\AnalysisResource;
use App\Jobs\AnalyzeResume;
use App\Models\Analysis;
use App\Models\Resume;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnalysisController extends Controller
{
    public function index(Request $request, Resume $resume)
    {
        $this->authorize('view', $resume);

        $analyses = $resume->analyses()
            ->with('feedback')
            ->orderByDesc('created_at')
            ->paginate(15);

        return AnalysisResource::collection($analyses);
    }

    public function store(CreateAnalysisRequest $request, Resume $resume): JsonResponse
    {
        $this->authorize('analyze', $resume);

        $validated = $request->validated();

        $analysis = $resume->analyses()->create([
            'type' => $validated['type'],
            'status' => 'pending',
            'job_description' => $validated['job_description'] ?? null,
        ]);

        AnalyzeResume::dispatch($analysis);

        return (new AnalysisResource($analysis))->response()->setStatusCode(201);
    }

    public function show(Request $request, Resume $resume, Analysis $analysis)
    {
        $this->authorize('view', $resume);

        $this->assertBelongsToResume($analysis, $resume);

        $analysis->load('feedback');

        return new AnalysisResource($analysis);
    }

    public function destroy(Request $request, Resume $resume, Analysis $analysis): JsonResponse
    {
        $this->authorize('view', $resume);

        $this->assertBelongsToResume($analysis, $resume);

        $analysis->delete();

        return response()->json(['message' => 'Analysis deleted']);
    }

    public function export(Request $request, Resume $resume, Analysis $analysis)
    {
        $this->authorize('view', $resume);

        $this->assertBelongsToResume($analysis, $resume);

        $user = $request->user();
        if (!$user->isPro()) {
            abort(403, 'PDF export requires Pro plan');
        }

        $analysis->load('feedback');

        $reportService = new ReportService();
        $path = $reportService->generatePdf($resume, $analysis);

        return Storage::disk('local')->download(
            $path,
            "resumeai-report-{$analysis->type}-{$resume->id}.pdf"
        );
    }

    public function status(Request $request, Resume $resume, Analysis $analysis)
    {
        $this->authorize('view', $resume);

        $this->assertBelongsToResume($analysis, $resume);

        return response()->json([
            'id' => $analysis->id,
            'status' => $analysis->status,
            'ats_score' => $analysis->ats_score,
            'completed_at' => $analysis->completed_at,
            'error_message' => $analysis->error_message,
        ]);
    }

    /**
     * An analysis id from another resume must read as missing, not forbidden,
     * so the URL cannot be used to probe for other people's records.
     */
    private function assertBelongsToResume(Analysis $analysis, Resume $resume): void
    {
        if ($analysis->resume_id !== $resume->id) {
            abort(404, 'Analysis not found');
        }
    }
}
