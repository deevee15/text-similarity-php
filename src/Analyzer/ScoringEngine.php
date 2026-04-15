<?php

    namespace PHPTextSimilarity\Analyzer;

    use PHPTextSimilarity\Config\WeightConfig;

    class ScoringEngine{
        public static function calculate(array $matches, array $firstTextEntities, array $secondTextEntities, array $titles){
            $textsScoring = [
                'proper' => count($matches['proper']) * WeightConfig::proper,
                'name' => count($matches['name']) * WeightConfig::names,
                'location' => count($matches['location']) * WeightConfig::locations,
                'abbreviation' => count($matches['abbreviation']) * WeightConfig::abbreviations,
                'organization' => count($matches['organization']) * WeightConfig::organizations,
                'other' => count($matches['other']) * WeightConfig::others
            ];

            //score entities mentions for text
            $textMentionsScore = self::textScore($matches, $firstTextEntities, $secondTextEntities); 

            //recompute score with text mentions
            $textsScoring = [
                'proper' => $textsScoring['proper'] * $textMentionsScore['proper'],
                'name' => $textsScoring['name'] * $textMentionsScore['name'],
                'location' => $textsScoring['location'] * $textMentionsScore['location'],
                'abbreviation' => $textsScoring['abbreviation'] * $textMentionsScore['abbreviation'],
                'organization' => $textsScoring['organization'] * $textMentionsScore['organization'],
                'other' => $textsScoring['other'] * $textMentionsScore['other']
            ];

            //score entities mentions in title for text
            $titleMentionsScore = self::titleScore($matches, $titles);

            //recompute score with title mentions
            $textsScoring = [
                'proper' => $textsScoring['proper'] * $titleMentionsScore['proper'],
                'name' => $textsScoring['name'] * $titleMentionsScore['name'],
                'location' => $textsScoring['location'] * $titleMentionsScore['location'],
                'abbreviation' => $textsScoring['abbreviation'] * $titleMentionsScore['abbreviation'],
                'organization' => $textsScoring['organization'] * $titleMentionsScore['organization'],
                'other' => $textsScoring['other'] * $titleMentionsScore['other']
            ];

            //points and words summation 
            $finalPoints = array_sum($textsScoring);
            $wordsCount = array_sum(array_map('count', $matches));

            $firstTextWordsCount = array_sum(array_map('count', $firstTextEntities)) - count($firstTextEntities['other_words']);
            $secondTextWordsCount = array_sum(array_map('count', $secondTextEntities)) - count($secondTextEntities['other_words']);

            $mutualAllWordsCount = max(
                $firstTextWordsCount, 
                $secondTextWordsCount
            );

            if ($mutualAllWordsCount > 10){
                $mutualAllWordsCount -= 3;
            }

            if($mutualAllWordsCount == 0){
                $res = [
                    'points' => $finalPoints,
                    'similarPercentage' => 0,
                    'probability' => 0,
                    'reason' => 'small words count',
                ];
                return $res;
            }

            $similarPercentage = $wordsCount / $mutualAllWordsCount;

            $probability = $finalPoints * $similarPercentage;
            
            $res = [
                'points' => $finalPoints,
                'similarPercentage' => $similarPercentage,
                'probability' => $probability,
                'otherWordsPoints' => $textsScoring['other'],
                'advancedProbability' => $probability + $textsScoring['other'],
            ];
            return $res;
        }

        private static function textScore(array $matches, array $firstTextEntities, array $secondTextEntities){
            $mentionsScore = [
                'proper' => 0,
                'name' => 0,
                'location' => 0,
                'abbreviation' => 0,
                'organization' => 0,
                'other' => 0
            ];

            foreach($matches as $entityType => $entityWords){
                $mentions = $entityType == 'proper' ? 1 : 0;
                foreach($entityWords as $singleRootWord){
                    $firstTextRootWordIndex = array_search(
                        $singleRootWord['root'], 
                        array_column($firstTextEntities[$entityType.'_words'], 'root')
                    );
                    $firstTextMentions = 0;

                    $secondTextRootWordIndex = array_search(
                        $singleRootWord['root'], 
                        array_column($secondTextEntities[$entityType.'_words'], 'root')
                    );
                    $secondTextMentions = 0;

                    if($firstTextRootWordIndex !== false and $secondTextRootWordIndex !== false){
                        $firstTextMentions = $firstTextEntities[$entityType.'_words'][$firstTextRootWordIndex]['mentions'];
                        $secondTextMentions = $secondTextEntities[$entityType.'_words'][$secondTextRootWordIndex]['mentions'];
                    }

                    $currentWordMentions = sqrt(($firstTextMentions + $secondTextMentions) / 2);
                    $mentions += $currentWordMentions;
                }

                if($mentions == 0) $mentions = 1;

                $mentionsScore[$entityType] = $mentions;
            }

            return $mentionsScore;
        }
        private static function titleScore(array $matches, array $titles){
            $mentionsScore = [
                'proper' => 1,
                'name' => 1,
                'location' => 1,
                'abbreviation' => 1,
                'organization' => 1,
                'other' => 1
            ];

            $titleFirstWords = explode(' ', mb_convert_case($titles['first'], MB_CASE_UPPER, 'UTF-8'));
            $titleSecondWords = explode(' ', mb_convert_case($titles['second'], MB_CASE_UPPER, 'UTF-8'));

            foreach($matches as $entityType => $entityWords){
                foreach($matches[$entityType] as $singleMatchWord){
                    $firstTextWordIndex = array_search($singleMatchWord['root'], $titleFirstWords);
                    $secondTextWordIndex = array_search($singleMatchWord['root'], $titleSecondWords);

                    if($firstTextWordIndex !== false and $secondTextWordIndex !== false){
                        $mentionsScore[$entityType] = 1.5;
                    }
                }
            }

            return $mentionsScore;
        }
    }