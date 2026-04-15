<?php

    namespace PHPTextSimilarity\Morphology;
    
    use PHPTextSimilarity\Service\Utils;

    class WordCache{
        private static $directory = __DIR__.'/../../words_cache/';

        public static function check(string $word){
            $translitWord = Utils::translit($word);

            $wordExists = self::$directory.$translitWord.'.txt';
            if(file_exists($wordExists)){
                $cachedWord = file_get_contents($wordExists);
                
                return $cachedWord;
            }
            else{
                return false;   
            }
        }
        public static function cache(array $wordData){
            $translitWord = Utils::translit($wordData['root']);
            
            if(!file_exists(self::$directory.$translitWord.'.txt')){ 
                file_put_contents(self::$directory.$translitWord.'.txt', json_encode($wordData), FILE_APPEND); 
            }
        }
    }


