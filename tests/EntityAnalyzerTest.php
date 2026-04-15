<?php

    namespace PHPTextSimilarity\Tests;

    use PHPUnit\Framework\TestCase;
    use PHPTextSimilarity\Analyzer\EntityAnalyzer;

    class EntityAnalyzerTest extends TestCase{
        public function testSplit(): void {
            $firstTextArray = [
                ['root' => 'APPLE', 'word_proper' => 1, 'name' => 0, 'location' => 0, 'abbreviation' => 0, 'organization' => 1, 'mentions' => 2],
                ['root' => 'CUPERTINO', 'word_proper' => 1, 'name' => 0, 'location' => 1, 'abbreviation' => 0, 'organization' => 0, 'mentions' => 1],
                ['root' => 'CALIFORNIA', 'word_proper' => 1, 'name' => 0, 'location' => 1, 'abbreviation' => 0, 'organization' => 0, 'mentions' => 1],
            ];

            $secondTextArray = [
                ['root' => 'APPLE', 'word_proper' => 1, 'name' => 0, 'location' => 0, 'abbreviation' => 0, 'organization' => 1, 'mentions' => 1],
                ['root' => 'CUPERTINO', 'word_proper' => 1, 'name' => 0, 'location' => 1, 'abbreviation' => 0, 'organization' => 0, 'mentions' => 1],
            ];

            $result = EntityAnalyzer::split($firstTextArray, $secondTextArray);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('matches', $result);
            $this->assertArrayHasKey('firstTextEntities', $result);
            $this->assertArrayHasKey('secondTextEntities', $result);
        }
    }
