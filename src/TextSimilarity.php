<?php

    namespace PHPTextSimilarity;

    use PHPTextSimilarity\Config\WeightConfig;

    use PHPTextSimilarity\Morphology\Adapter;
    use PHPTextSimilarity\Analyzer\ScoringEngine;
    use PHPTextSimilarity\Analyzer\EntityAnalyzer;

    class TextSimilarity{
        private static $allowedLangs = ['en', 'ru'];
        
        public static function compare(string $lang, string $firstText, string $secondText, array $titles){
            if(!in_array($lang, self::$allowedLangs)) throw new \Exception('Language not supported');
            if($firstText === '' || $secondText === '') throw new \Exception('Text cannot be empty');
            if(count($titles) === 0 or empty($titles['first']) or empty($titles['second'])) throw new \Exception('Titles cannot be empty');
            
            $firstTextProcessed = Adapter::processText($firstText, $lang);
            $secondTextProcessed = Adapter::processText($secondText, $lang);

            $splitResult = EntityAnalyzer::split($firstTextProcessed, $secondTextProcessed);

            $calculatedResult = ScoringEngine::calculate($splitResult['matches'], $splitResult['firstTextEntities'], $splitResult['secondTextEntities'], $titles);

            $similarBool = false;
            if($calculatedResult['probability'] >= WeightConfig::similar_marker and $calculatedResult['otherWordsPoints'] >= WeightConfig::otherWords_marker){
                $similarBool = true;
            }

            return [
                'score' => $calculatedResult['probability'],
                'matches' => $splitResult['matches'],
                'firstTextEntities' => $splitResult['firstTextEntities'],
                'secondTextEntities' => $splitResult['secondTextEntities'],
                'similar' => $similarBool
            ];
        }
    }

