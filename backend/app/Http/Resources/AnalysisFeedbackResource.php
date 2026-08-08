<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'analysis_id' => $this->analysis_id,
            'category' => $this->category,
            'severity' => $this->severity,
            'message' => $this->message,
            'suggestion' => $this->suggestion,
            'section' => $this->section,
            'created_at' => $this->created_at,
        ];
    }
}
