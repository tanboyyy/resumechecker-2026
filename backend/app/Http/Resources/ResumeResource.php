<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_human' => $this->getFileSizeHuman(),
            'text_extracted' => $this->text_extracted,
            'extraction_status' => $this->extraction_status,
            'extraction_error' => $this->extraction_error,
            'extracted_text' => $this->when($request->routeIs('resumes.show'), $this->extracted_text),
            'analyses_count' => $this->whenCounted('analyses'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
