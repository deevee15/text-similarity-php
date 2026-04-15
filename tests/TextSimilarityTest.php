<?php

    namespace PHPTextSimilarity\Tests;

    use PHPUnit\Framework\TestCase;
    use PHPTextSimilarity\TextSimilarity;

    class TextSimilarityTest extends TestCase{
        public function testCompare(): void {
            $result = TextSimilarity::compare(
                'en',
                'Apple Inc. is a technology company based in Cupertino, California.', 
                'Apple is a tech company located in Cupertino, California.', 
                ['first' => 'What is Apple?', 'second' => 'Is Apple a technology company?']
            );
            $this->assertIsArray($result);
            $this->assertArrayHasKey('score', $result);
            $this->assertArrayHasKey('matches', $result);
            $this->assertArrayHasKey('firstTextEntities', $result);
            $this->assertArrayHasKey('secondTextEntities', $result);
            $this->assertArrayHasKey('similar', $result);
        }

        public function testEmptyTextThrowsException(): void {
            $this->expectException(\Exception::class);
            TextSimilarity::compare('ru', '', 'текст', ['first' => ['a'], 'second' => ['b']]);
        }
        public function testUnsupportedLanguageThrowsException(): void {
            $this->expectException(\Exception::class);
            TextSimilarity::compare('de', 'Text', 'Text', ['first' => ['a'], 'second' => ['b']]);
        }
        public function testEmptyTitles():void {
            $this->expectException(\Exception::class);
            $result = TextSimilarity::compare('en', 'Hello world', 'Hello world', ['first' => [], 'second' => []]);
        }
    }