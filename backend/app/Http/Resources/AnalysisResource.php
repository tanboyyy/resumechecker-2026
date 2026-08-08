<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'resume_id' => $this->resume_id,
            'type' => $this->type,
            'status' => $this->status,
            'ats_score' => $this->ats_score,
            'result' => $this->when($this->isCompleted(), $this->result),
            'job_description' => $this->when(
                $this->type === 'comparison',
                $this->job_description
            ),
            'tokens_used' => $this->tokens_used,
            'error_message' => $this->when($this->isFailed(), $this->error_message),
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'feedback' => AnalysisFeedbackResource::collection(
                $this->whenLoaded('feedback')
            ),
        ];
    }
}
