export interface User {
  id: number
  name: string
  email: string
  avatar: string | null
  provider: string
}

export interface Resume {
  id: number
  title: string
  original_filename: string
  mime_type: string
  size: number
  size_human: string
  text_extracted: boolean
  extracted_text?: string
  analyses_count: number
  created_at: string
  updated_at: string
}

export interface AnalysisFeedback {
  id: number
  analysis_id: number
  category: string
  severity: 'critical' | 'warning' | 'info' | 'success'
  message: string
  suggestion: string | null
  section: string | null
  created_at: string
}

export interface Analysis {
  id: number
  resume_id: number
  type: 'ats' | 'content' | 'formatting' | 'comparison'
  status: 'pending' | 'processing' | 'completed' | 'failed'
  ats_score: number | null
  raw_response: Record<string, unknown> | null
  job_description: string | null
  tokens_used: number | null
  error_message: string | null
  completed_at: string | null
  created_at: string
  feedback: AnalysisFeedback[]
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}
