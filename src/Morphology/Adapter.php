<?php

    namespace PHPTextSimilarity\Morphology;
    
    use phpMorphy;

    use PHPTextSimilarity\Morphology\WordCache;

    class Adapter{
        private static $langPatterns = [
            'ru' => '/[аА-яЯ]/',
            'en' => '/[aA-zZ]/'
        ];

        public static function processText(string $text, string $lang){
            $textSplitted = explode(' ', $text);

            //remove all dots from text's array
            $textNoDots = self::removeDots($textSplitted, $lang);
            //clear text array from short words with length less than 3 symbols and clear trashed words
            $textNoShorts = self::clearShortWords($textNoDots);
            //remove total duplicates
            $textNoDuplicates = self::removeDuplicates($textNoShorts);

            //convert all text array to upper case for phpmorphy lib
            $upperCaseText = [];
            foreach($textNoDuplicates as $singleWord){
                $one_word = $singleWord;
                $one_word['root'] =  mb_convert_case(trim($singleWord['root']), MB_CASE_UPPER, 'UTF-8');
                $upperCaseText[] = $one_word;
            }

            $phpMorphy = new \cijic\phpMorphy\Morphy($lang);
            $morphyText = [
                'paradigms' => $phpMorphy->findWord(array_column($upperCaseText, 'root')),
                'wordsInfo' => $upperCaseText
            ];
            //process each word for collecting entities for scoring
            $processedParadigms = self::processParadigms($morphyText);
            //count mentions for each word and remove same words with different paradigms
            $finalProcessedText = self::processCognate($processedParadigms);

            return $finalProcessedText;
        }

        private static function removeDots(array $textSplitted, string $lang){
            $upperWordsCount = 0;
            $processedTextArray = [];
            foreach($textSplitted as $num => $oneWord){
                //if this word has dot
                if(mb_strpos($oneWord, '.', 0, 'UTF-8')) {
                    $oneWordProcessed = explode('.', $oneWord);
                    foreach($oneWordProcessed as $singleWord){
                        preg_match(self::$langPatterns[$lang], $singleWord, $checkWord);

                        if($checkWord){
                            $processedTextArray[] = [
                                'root' => $singleWord,
                                'word_upper_sentence' => 1
                            ];
                        }
                        else{
                            $upperWordsCount = $num + 1;
                            continue;
                        }  
                    }
                }
                //word has no dot
                else{
                    if($num != $upperWordsCount){
                        $processedTextArray[] = [
                            'root'=> $oneWord,
                            'word_upper_sentence' => 0
                        ];
                    }
                    else{
                        $processedTextArray[] = [
                            'root'=> $oneWord,
                            'word_upper_sentence' => 1
                        ];
                    }
                }
            }

            return $processedTextArray;
        }

        private static function clearShortWords(array $textSplitted){
            $convertedTextArray = [];

            $trashSymbols = ['<', '>', '/', '.', ',', ':', ';', '"', '-','(', ')', '\n'];
            foreach($textSplitted as $singleWord){
                $wordRoot = $singleWord['root'];

                $charset = mb_detect_encoding($wordRoot);
                $unicode_string = iconv($charset, 'UTF-8', $wordRoot);

                $wordRoot = trim($wordRoot);
                $wordRoot = str_replace($trashSymbols, '', $wordRoot);

                preg_match('/[A-Za-z,[1-9]/m', $wordRoot, $check_word);
                if($check_word) continue;

                if($wordRoot === mb_convert_case(trim($wordRoot), MB_CASE_UPPER, 'UTF-8')){
                    if(mb_strlen($wordRoot, 'UTF-8') >= 2) {
                        $convertedTextArray[] = [
                            'root' => $wordRoot,
                            'mentions' => 1,
                            'word_proper' => 0,
                            'word_allupper_article' => 1,
                            'word_upper_sentence' => $singleWord['word_upper_sentence']
                        ];
                    }
                }
                elseif($wordRoot != mb_convert_case($wordRoot, MB_CASE_TITLE, 'UTF-8')){
                    if(mb_strlen($wordRoot, 'UTF-8') >= 3) {
                        $convertedTextArray[] = [
                            'root' => $wordRoot,
                            'mentions' => 1,
                            'word_proper' => 0,
                            'word_allupper_article' => 0,
                            'word_upper_sentence' => $singleWord['word_upper_sentence']
                        ];
                    }
                }
                elseif($wordRoot == mb_convert_case($wordRoot, MB_CASE_TITLE, 'UTF-8') and $singleWord['word_upper_sentence'] != 1){
                    $convertedTextArray[] = [
                        'root' => $wordRoot,
                        'mentions' => 1,
                        'word_proper' => 1,
                        'word_allupper_article' => 0,
                        'word_upper_sentence' => $singleWord['word_upper_sentence']
                    ];
                }
            }
            return $convertedTextArray;
        }

        private static function removeDuplicates(array $textSplitted){
            $noDuplicatesText = [];

            foreach($textSplitted as $singleWord){
                $wordIndex = array_search($singleWord['root'], array_column($noDuplicatesText, 'root'));
                if($wordIndex !== false){
                    $one_word = $singleWord['root'];

                    if($one_word === mb_convert_case(trim($one_word), MB_CASE_UPPER, 'UTF-8')){
                        if(mb_strlen($one_word, "UTF-8") >= 2){
                            $noDuplicatesText[$wordIndex]['word_allupper_article'] += 1;
                        }
                    }
                    if($singleWord['word_upper_sentence'] >= 1) {
                        $noDuplicatesText[$wordIndex]['word_upper_sentence'] += 1;
                    }

                    $noDuplicatesText[$wordIndex]['mentions'] += 1;
                }
                else{
                    $noDuplicatesText[] = $singleWord;
                }
            }
            return $noDuplicatesText;
        }
        private static function processParadigms(array $morphyText){
            $processedParadigms = [];

            $wordsInfo = $morphyText['wordsInfo'];
            
            foreach($morphyText['paradigms'] as $key => $oneWord){
                $checkedWord = WordCache::check($key);

                if($checkedWord !== false){
                    $processedParadigms[] = json_decode($checkedWord, true);
                }
                else{
                    $wordParadigms = $oneWord;
                    $morphiedWord = [];

                    $needleWordIndex = array_search($key, array_column($wordsInfo, 'root'));
                    if($needleWordIndex === false) continue;

                    $morphiedWord['mentions'] = $wordsInfo[$needleWordIndex]['mentions'];
                    $morphiedWord['word_upper_sentence'] = $wordsInfo[$needleWordIndex]['word_upper_sentence'];
                    $morphiedWord['word_proper'] = $wordsInfo[$needleWordIndex]['word_proper'];
                    $morphiedWord['word_allupper_article'] = $wordsInfo[$needleWordIndex]['word_allupper_article'];

                    $partOfSpeech = '';

                    foreach($wordParadigms as $paradigm) {
                        $found_word_ary = $paradigm->getFoundWordForm();
                        foreach($found_word_ary as $found_form){$partOfSpeech = $found_form->getPartOfSpeech();}
                        
                        if($paradigm->hasGrammems('НО')) {
                            $morphiedWord['animated'] = 0;
                        }
                        else{
                            $morphiedWord['animated'] = 1;
                        }
                        //set part of speech and word's root form
                        $morphiedWord['partofspeech'] = $partOfSpeech;
                        $morphiedWord['root'] = $paradigm->getBaseForm();
                        //skip word if its not noun
                        if($partOfSpeech != 'С') continue;
                        //location
                        if($paradigm->hasGrammems('ЛОК')) {
                            $morphiedWord['location'] = 1;
                        }
                        else{
                            $morphiedWord['location'] = 0;
                        }

                        if($paradigm->hasGrammems('ОРГ')) {
                            $morphiedWord['organization'] = 1;
                        }
                        else{
                            $morphiedWord['organization'] = 0;
                        }

                        if($paradigm->hasGrammems('ИМЯ') or $paradigm->hasGrammems('ФАМ') or $paradigm->hasGrammems('ОТЧ')){
                            $morphiedWord['name'] = 1;
                        }
                        else{
                            $morphiedWord['name'] = 0;
                        }

                        if($paradigm->hasGrammems('АББР')) {
                            $morphiedWord['abbreviation'] = 1;
                        }
                        else{
                            $morphiedWord['abbreviation'] = 0;
                        }

                    }

                    $processedParadigms[] = $morphiedWord;
                    
                    WordCache::cache($morphiedWord);
                }
            }

            return $processedParadigms;
        }
        private static function processCognate(array $textSplitted){
            $countedText = [];
            foreach($textSplitted as $singleWord){
                $wordIndex = array_search($singleWord['root'], array_column($countedText, 'root'));
                if($wordIndex !== false){
                    //increment mentions if root already exists
                    $countedText[$wordIndex]['mentions'] += 1;
                }
                else{
                    $countedText[] = $singleWord;
                }
            }

            return $countedText;
        }
    }