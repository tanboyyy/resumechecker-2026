<?php

namespace Tests\Unit;

use App\Services\AnalysisResultNormalizer;
use PHPUnit\Framework\TestCase;

class AnalysisResultNormalizerTest extends TestCase
{
    private AnalysisResultNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new AnalysisResultNormalizer();
    }

    public function test_it_reads_a_well_formed_response(): void
    {
        $result = $this->normalizer->normalize([
            'ats_score' => 72,
            'summary' => 'A solid resume with gaps in quantification.',
            'strengths' => ['Clear contact details'],
            'weaknesses' => ['No professional summary'],
            'overall_recommendations' => ['Add measurable outcomes'],
            'feedback' => [[
                'category' => 'Experience',
                'severity' => 'critical',
                'message' => 'Bullet points describe duties, not results.',
                'suggestion' => 'Lead with an outcome and a number.',
                'section' => 'Work Experience',
            ]],
        ]);

        $this->assertSame(72, $result['score']);
        $this->assertSame('A solid resume with gaps in quantification.', $result['summary']);
        $this->assertSame(['Clear contact details'], $result['strengths']);
        $this->assertSame(['Add measurable outcomes'], $result['recommendations']);
        $this->assertCount(1, $result['feedback']);
        $this->assertSame('critical', $result['feedback'][0]['severity']);
        $this->assertTrue($this->normalizer->isUsable($result));
    }

    /**
     * The exact shape that silently produced a scoreless, feedbackless
     * "completed" analysis in production.
     */
    public function test_it_unwraps_a_single_key_envelope(): void
    {
        $result = $this->normalizer->normalize([
            'evaluation' => [
                'ats_score' => 45,
                'strengths' => ['Good categorisation of technical skills.'],
                'weaknesses' => ['Work experience section is missing entirely.'],
            ],
        ]);

        $this->assertSame(45, $result['score']);
        $this->assertSame(['Good categorisation of technical skills.'], $result['strengths']);
        $this->assertTrue($this->normalizer->isUsable($result));
    }

    public function test_it_unwraps_nested_envelopes(): void
    {
        $result = $this->normalizer->normalize([
            'result' => ['analysis' => ['score' => 60, 'summary' => 'Fine.']],
        ]);

        $this->assertSame(60, $result['score']);
        $this->assertSame('Fine.', $result['summary']);
    }

    public function test_it_accepts_alternative_key_names(): void
    {
        $result = $this->normalizer->normalize([
            'match_score' => 81,
            'match_analysis' => 'Strong alignment on backend requirements.',
            'issues' => [[
                'area' => 'Keywords',
                'priority' => 'medium',
                'issue' => 'Missing "Kubernetes".',
                'fix' => 'Add it to the skills list.',
            ]],
        ]);

        $this->assertSame(81, $result['score']);
        $this->assertSame('Strong alignment on backend requirements.', $result['summary']);
        $this->assertSame('Keywords', $result['feedback'][0]['category']);
        $this->assertSame('warning', $result['feedback'][0]['severity']);
        $this->assertSame('Missing "Kubernetes".', $result['feedback'][0]['message']);
        $this->assertSame('Add it to the skills list.', $result['feedback'][0]['suggestion']);
    }

    public function test_it_coerces_scores_expressed_as_text(): void
    {
        $this->assertSame(72, $this->normalizer->normalize(['ats_score' => '72/100'])['score']);
        $this->assertSame(88, $this->normalizer->normalize(['ats_score' => '88'])['score']);
        $this->assertSame(65, $this->normalizer->normalize(['ats_score' => 65.4])['score']);
    }

    public function test_it_clamps_scores_to_the_valid_range(): void
    {
        $this->assertSame(100, $this->normalizer->normalize(['ats_score' => 140])['score']);
        $this->assertSame(0, $this->normalizer->normalize(['ats_score' => -20])['score']);
    }

    public function test_unknown_severities_fall_back_to_info(): void
    {
        $result = $this->normalizer->normalize([
            'feedback' => [
                ['message' => 'a', 'severity' => 'catastrophic'],
                ['message' => 'b', 'severity' => 'HIGH'],
                ['message' => 'c'],
            ],
        ]);

        $this->assertSame('info', $result['feedback'][0]['severity']);
        $this->assertSame('critical', $result['feedback'][1]['severity']);
        $this->assertSame('info', $result['feedback'][2]['severity']);
    }

    public function test_feedback_entries_without_a_message_are_dropped(): void
    {
        $result = $this->normalizer->normalize([
            'feedback' => [
                ['category' => 'Formatting'],
                ['message' => 'Inconsistent date formats.'],
            ],
        ]);

        $this->assertCount(1, $result['feedback']);
        $this->assertSame('Inconsistent date formats.', $result['feedback'][0]['message']);
    }

    public function test_a_response_with_neither_score_nor_feedback_is_not_usable(): void
    {
        $result = $this->normalizer->normalize(['notes' => 'I could not analyse this.']);

        $this->assertNull($result['score']);
        $this->assertSame([], $result['feedback']);
        $this->assertFalse($this->normalizer->isUsable($result));
    }
}
