<?php

    namespace PHPTextSimilarity\Analyzer;

    class EntityAnalyzer{
        private static $textEntities = [
            'proper_words' => [],
            'name_words' => [],
            'location_words' => [],
            'abbreviation_words' => [],
            'organization_words' => [],
            'other_words' => []
        ];
        public static function split(array $firstTextArray, array $secondTextArray){
            //set entities structure for each text
            $firstTextEntities = self::setEntities($firstTextArray);
            $secondTextEntities = self::setEntities($secondTextArray);

            //collect words matches from two texts
            $matches = self::countMatches($firstTextEntities, $secondTextEntities);

            return [
                'matches' => $matches,
                'firstTextEntities' => $firstTextEntities,
                'secondTextEntities' => $secondTextEntities,
            ];
        }

        private static function setEntities(array $textArray){
            $textEntities = self::$textEntities;
            foreach($textArray as $oneWord){
                if($oneWord['word_proper'] == 1) $textEntities['proper_words'][] = $oneWord;
                else if($oneWord['name'] == 1) $textEntities['name_words'][] = $oneWord;
                else if($oneWord['location'] == 1) $textEntities['location_words'][] = $oneWord;
                else if($oneWord['abbreviation'] == 1) $textEntities['abbreviation_words'][] = $oneWord;
                else if($oneWord['organization'] == 1) $textEntities['organization_words'][] = $oneWord;
                else $textEntities['other_words'][] = $oneWord;
            }
            
            return $textEntities;
        }
        private static function countMatches(array $firstTextEntities, array $secondTextEntities){
            $wordsList = [
                'proper' => [],
                'name' => [],
                'location' => [],
                'abbreviation' => [],
                'organization' => [],
                'other' => []
            ];
            $entityTypes = array_keys(self::$textEntities);

            foreach($entityTypes as $entityType){
                foreach($secondTextEntities[$entityType] as $secondTextWord){
                    if(in_array($secondTextWord, $firstTextEntities[$entityType])){
                        $clearType = str_replace('_words', '', $entityType);

                        $wordsList[$clearType][] = $secondTextWord;
                    }
                    else{
                        continue;
                    }
                }
            }

            return $wordsList;
        }
    }
