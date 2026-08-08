<?php

namespace App\Services;

/**
 * Turns whatever the model returned into the one shape the rest of the app uses.
 *
 * Models drift: they wrap the object in an envelope, rename the score, call the
 * findings "issues" instead of "feedback", or invent severity words. Prompting
 * and response_format reduce that but do not eliminate it, so every response is
 * reconciled here before anything is persisted.
 */
class AnalysisResultNormalizer
{
    private const SCORE_KEYS = [
        'ats_score', 'score', 'match_score', 'overall_score',
        'compatibility_score', 'content_score', 'formatting_score',
    ];

    private const SUMMARY_KEYS = [
        'summary', 'match_analysis', 'overall', 'overall_assessment', 'assessment',
    ];

    private const FEEDBACK_KEYS = [
        'feedback', 'findings', 'issues', 'items', 'suggestions',
    ];

    private const SEVERITY_MAP = [
        'critical' => 'critical', 'high' => 'critical', 'severe' => 'critical', 'error' => 'critical',
        'warning' => 'warning', 'warn' => 'warning', 'medium' => 'warning', 'moderate' => 'warning',
        'info' => 'info', 'low' => 'info', 'suggestion' => 'info', 'minor' => 'info', 'note' => 'info',
    ];

    /**
     * @return array{
     *     score: int|null,
     *     summary: string|null,
     *     strengths: list<string>,
     *     weaknesses: list<string>,
     *     recommendations: list<string>,
     *     keywords_matched: list<string>,
     *     keywords_missing: list<string>,
     *     gaps: list<string>,
     *     feedback: list<array{category: string, severity: string, message: string, suggestion: string|null, section: string|null}>
     * }
     */
    public function normalize(array $content): array
    {
        $root = $this->unwrap($content);

        return [
            'score' => $this->score($root),
            'summary' => $this->firstString($root, self::SUMMARY_KEYS),
            'strengths' => $this->stringList($root, ['strengths', 'pros']),
            'weaknesses' => $this->stringList($root, ['weaknesses', 'cons', 'problems']),
            'recommendations' => $this->stringList($root, ['overall_recommendations', 'recommendations', 'next_steps']),
            'keywords_matched' => $this->stringList($root, ['matching_keywords', 'matched_keywords', 'keywords_matched']),
            'keywords_missing' => $this->stringList($root, ['missing_keywords', 'keywords_missing']),
            'gaps' => $this->stringList($root, ['gaps', 'missing_requirements']),
            'feedback' => $this->feedback($root),
        ];
    }

    /**
     * A result is only worth showing if it carries a score or at least one finding.
     */
    public function isUsable(array $normalized): bool
    {
        return $normalized['score'] !== null || $normalized['feedback'] !== [];
    }

    /**
     * Descend through single-key envelopes such as {"evaluation": {...}} or
     * {"result": {"analysis": {...}}} until the real object is reached.
     */
    private function unwrap(array $content, int $depth = 0): array
    {
        if ($depth >= 4 || $this->looksLikeResult($content)) {
            return $content;
        }

        if (count($content) === 1) {
            $only = reset($content);

            if (is_array($only)) {
                return $this->unwrap($only, $depth + 1);
            }
        }

        return $content;
    }

    private function looksLikeResult(array $content): bool
    {
        foreach ([...self::SCORE_KEYS, ...self::FEEDBACK_KEYS, ...self::SUMMARY_KEYS] as $key) {
            if (array_key_exists($key, $content)) {
                return true;
            }
        }

        return false;
    }

    private function score(array $root): ?int
    {
        foreach (self::SCORE_KEYS as $key) {
            $value = $root[$key] ?? null;

            if (is_int($value) || is_float($value)) {
                return $this->clamp((int) round($value));
            }

            // Models sometimes emit "72" or "72/100".
            if (is_string($value) && preg_match('/-?\d+/', $value, $m)) {
                return $this->clamp((int) $m[0]);
            }
        }

        return null;
    }

    private function clamp(int $value): int
    {
        return max(0, min(100, $value));
    }

    private function firstString(array $root, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $root[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function stringList(array $root, array $keys): array
    {
        foreach ($keys as $key) {
            $value = $root[$key] ?? null;

            if (!is_array($value)) {
                continue;
            }

            $items = [];

            foreach ($value as $item) {
                // Entries are usually strings but occasionally {"text": "..."}.
                $text = is_string($item)
                    ? $item
                    : (is_array($item) ? ($item['text'] ?? $item['description'] ?? $item['message'] ?? null) : null);

                if (is_string($text) && trim($text) !== '') {
                    $items[] = trim($text);
                }
            }

            if ($items !== []) {
                return array_values(array_unique($items));
            }
        }

        return [];
    }

    /**
     * @return list<array{category: string, severity: string, message: string, suggestion: string|null, section: string|null}>
     */
    private function feedback(array $root): array
    {
        foreach (self::FEEDBACK_KEYS as $key) {
            $value = $root[$key] ?? null;

            if (!is_array($value)) {
                continue;
            }

            $items = [];

            foreach ($value as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $message = $this->firstString($item, ['message', 'issue', 'text', 'problem', 'title']);

                if ($message === null) {
                    continue;
                }

                $items[] = [
                    'category' => $this->firstString($item, ['category', 'type', 'area']) ?? 'General',
                    'severity' => $this->severity($item['severity'] ?? $item['priority'] ?? null),
                    'message' => $message,
                    'suggestion' => $this->firstString($item, ['suggestion', 'fix', 'recommendation', 'action']),
                    'section' => $this->firstString($item, ['section', 'location']),
                ];
            }

            if ($items !== []) {
                return $items;
            }
        }

        return [];
    }

    private function severity(mixed $value): string
    {
        if (!is_string($value)) {
            return 'info';
        }

        return self::SEVERITY_MAP[strtolower(trim($value))] ?? 'info';
    }
}
