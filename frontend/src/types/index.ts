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

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}
