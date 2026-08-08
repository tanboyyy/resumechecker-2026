<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resume\StoreResumeRequest;
use App\Http\Resources\ResumeResource;
use App\Models\Resume;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResumeController extends Controller
{
    public function index(Request $request)
    {
        $resumes = $request->user()
            ->resumes()
            ->withCount('analyses')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return ResumeResource::collection($resumes);
    }

    public function store(StoreResumeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $file = $request->file('file');
        $title = $validated['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $disk = config('filesystems.default', 'local');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('resumes', $filename, $disk);

        $resume = $request->user()->resumes()->create([
            'title' => $title,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return (new ResumeResource($resume))->response()->setStatusCode(201);
    }

    public function show(Request $request, Resume $resume)
    {
        $this->authorize('view', $resume);

        $resume->loadCount('analyses');

        return new ResumeResource($resume);
    }

    public function destroy(Request $request, Resume $resume): JsonResponse
    {
        $this->authorize('delete', $resume);

        $resume->deleteFile();
        $resume->delete();

        return response()->json(['message' => 'Resume deleted']);
    }

    public function download(Request $request, Resume $resume)
    {
        $this->authorize('view', $resume);

        $disk = $this->diskFor($resume);

        if ($resume->disk === 'local') {
            return $disk->download($resume->storage_path, $resume->original_filename);
        }

        return response()->redirectTo($resume->getTemporaryUrl(300));
    }

    public function viewPdf(Request $request, Resume $resume)
    {
        $this->authorize('view', $resume);

        $disk = $this->diskFor($resume);

        if ($resume->disk === 'local') {
            return $disk->response($resume->storage_path, $resume->original_filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $resume->original_filename . '"',
            ]);
        }

        return response()->redirectTo($resume->getTemporaryUrl(300));
    }

    /**
     * Resolve the resume's disk, failing with a 404 rather than a 500 when the
     * stored file has gone missing underneath the database row.
     */
    private function diskFor(Resume $resume): Filesystem
    {
        $disk = Storage::disk($resume->disk);

        if (!$disk->exists($resume->storage_path)) {
            abort(404, 'This file is no longer available.');
        }

        return $disk;
    }
}
