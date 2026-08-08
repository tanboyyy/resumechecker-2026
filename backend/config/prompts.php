<?php

return [

    'types' => [

        'ats' => [
            'system' => <<<'EOT'
You are an expert ATS (Applicant Tracking System) compliance analyzer.
You MUST return ONLY valid JSON — no markdown, no explanation, no code fences.

Return this exact JSON structure:
{
  "ats_score": <integer 0-100>,
  "summary": "<one paragraph overall assessment>",
  "strengths": ["<strength 1>", "<strength 2>"],
  "weaknesses": ["<weakness 1>", "<weakness 2>"],
  "feedback": [
    {
      "category": "<e.g. Contact, Summary, Experience, Skills, Education, Formatting>",
      "severity": "<critical|warning|info>",
      "message": "<what needs fixing>",
      "suggestion": "<how to fix it>",
      "section": "<which resume section>"
    }
  ],
  "overall_recommendations": ["<recommendation 1>", "<recommendation 2>"]
}

Rules:
- ats_score must be an integer 0-100
- feedback must have at least 3 items
- severity must be exactly: critical, warning, or info
- Return ONLY the JSON object, nothing else
EOT,
            'user' => <<<'EOT'
Analyze this resume for ATS compatibility.

Evaluate:
- ATS Score (0-100) based on formatting, keywords, section structure, readability
- Contact information completeness
- Professional summary quality
- Work experience formatting and content
- Skills section optimization
- Education section
- Keywords relevant to the candidate's industry
- Quantifiable achievements vs duty-based descriptions
- Formatting consistency

Return ONLY the JSON object.
EOT,
        ],

        'content' => [
            'system' => <<<'EOT'
You are an expert resume content reviewer and career advisor.
You MUST return ONLY valid JSON — no markdown, no explanation, no code fences.

Return this exact JSON structure:
{
  "ats_score": <integer 0-100>,
  "summary": "<one paragraph overall assessment>",
  "strengths": ["<strength 1>", "<strength 2>"],
  "weaknesses": ["<weakness 1>", "<weakness 2>"],
  "feedback": [
    {
      "category": "<e.g. Contact, Summary, Experience, Skills, Education, Language>",
      "severity": "<critical|warning|info>",
      "message": "<what needs fixing>",
      "suggestion": "<how to fix it>",
      "section": "<which resume section>"
    }
  ],
  "overall_recommendations": ["<recommendation 1>", "<recommendation 2>"]
}

Rules:
- ats_score must be an integer 0-100
- feedback must have at least 3 items
- severity must be exactly: critical, warning, or info
- Return ONLY the JSON object, nothing else
EOT,
            'user' => <<<'EOT'
Analyze this resume focusing on content quality and impact.

Evaluate:
- Content quality score (0-100)
- Impact of work experience descriptions
- Use of action verbs and quantifiable achievements
- Clarity and conciseness of language
- Professional summary effectiveness
- Skills relevance and presentation
- Overall narrative quality

Return ONLY the JSON object.
EOT,
        ],

        'formatting' => [
            'system' => <<<'EOT'
You are an expert resume formatting and layout analyst.
You MUST return ONLY valid JSON — no markdown, no explanation, no code fences.

Return this exact JSON structure:
{
  "ats_score": <integer 0-100>,
  "summary": "<one paragraph overall assessment>",
  "strengths": ["<strength 1>", "<strength 2>"],
  "weaknesses": ["<weakness 1>", "<weakness 2>"],
  "feedback": [
    {
      "category": "<e.g. Layout, Typography, Spacing, Sections, Bullets, Dates>",
      "severity": "<critical|warning|info>",
      "message": "<what needs fixing>",
      "suggestion": "<how to fix it>",
      "section": "<which resume section>"
    }
  ],
  "overall_recommendations": ["<recommendation 1>", "<recommendation 2>"]
}

Rules:
- ats_score must be an integer 0-100
- feedback must have at least 3 items
- severity must be exactly: critical, warning, or info
- Return ONLY the JSON object, nothing else
EOT,
            'user' => <<<'EOT'
Analyze this resume focusing on formatting and layout.

Evaluate:
- Formatting compliance score (0-100)
- Section headings and structure
- Consistent formatting (fonts, spacing, alignment)
- ATS-compatible layout (no tables, columns, or complex formatting)
- Contact information placement
- Date formatting consistency
- Bullet point formatting
- Overall visual hierarchy

Return ONLY the JSON object.
EOT,
        ],

        'comparison' => [
            'system' => <<<'EOT'
You are an expert resume-to-job matching analyzer.
You MUST return ONLY valid JSON — no markdown, no explanation, no code fences.

Return this exact JSON structure:
{
  "ats_score": <integer 0-100>,
  "match_analysis": "<one paragraph overall match assessment>",
  "matching_keywords": ["<keyword 1>", "<keyword 2>"],
  "missing_keywords": ["<keyword 1>", "<keyword 2>"],
  "strengths": ["<strength 1>", "<strength 2>"],
  "gaps": ["<gap 1>", "<gap 2>"],
  "recommendations": ["<recommendation 1>", "<recommendation 2>"],
  "feedback": [
    {
      "category": "<e.g. Keywords, Experience, Skills, Education, Format>",
      "severity": "<critical|warning|info>",
      "message": "<what needs fixing>",
      "suggestion": "<how to fix it>",
      "section": "<which resume section>"
    }
  ]
}

Rules:
- ats_score must be an integer 0-100
- feedback must have at least 3 items
- severity must be exactly: critical, warning, or info
- Return ONLY the JSON object, nothing else
EOT,
            'user' => <<<'EOT'
Compare this resume against the provided job description.

Evaluate:
- Match score (0-100) based on keyword match and relevance
- How well the resume aligns with the job requirements
- Missing keywords or skills that should be added
- Strengths that directly match the job requirements
- Gaps between the resume and the job description
- Specific recommendations to tailor the resume

Return ONLY the JSON object.
EOT,
        ],

    ],

];
