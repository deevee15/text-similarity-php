<?php

    namespace PHPTextSimilarity\Tests;

    use PHPUnit\Framework\TestCase;
    use PHPTextSimilarity\Analyzer\ScoringEngine;

    class ScoringEngineTest extends TestCase{
        public function testCalculate(): void {
            $word = ['root' => 'APPLE', 'mentions' => 2, 'word_proper' => 1, 'name' => 0, 'location' => 0, 'abbreviation' => 0, 'organization' => 1];

            $matches = [
                'proper' => [$word],
                'name' => [],
                'location' => [],
                'abbreviation' => [],
                'organization' => [$word],
                'other' => []
            ];

            $firstTextEntities = [
                'proper_words' => [$word],
                'name_words' => [],
                'location_words' => [],
                'abbreviation_words' => [],
                'organization_words' => [$word],
                'other_words' => []
            ];

            $secondTextEntities = [
                'proper_words' => [$word],
                'name_words' => [],
                'location_words' => [],
                'abbreviation_words' => [],
                'organization_words' => [$word],
                'other_words' => []
            ];

            $titles = [
                'first' => 'What is Apple?',
                'second' => 'Is Apple a technology company?'
            ];

            $result = ScoringEngine::calculate($matches, $firstTextEntities, $secondTextEntities, $titles);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('points', $result);
            $this->assertArrayHasKey('probability', $result);
            $this->assertGreaterThan(0, $result['points']);
        }
    }