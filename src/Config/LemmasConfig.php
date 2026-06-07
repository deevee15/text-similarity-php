<?php

    namespace PHPTextSimilarity\Config;

    class LemmasConfig{
        public const LIST = [
            'ru' => [
                'noun' => 'С',
                'location' => 'ЛОК',
                'organization' => 'ОРГ',
                'name' => 'ИМЯ',
                'animal' => 'НО',
                'abbreviation' => 'АББР',
            ],
            'en' => [
                'noun' => 'NOUN',
                'location' => 'GEO',
                'organization' => 'ORG',
                'name' => 'NAME',
                'animal' => null,
                'abbreviation' => 'ABBR',
            ],
            'de' => [
                'noun' => 'SUB',
                'location' => 'GEO',
                'organization' => 'ORG',
                'name' => 'PER',
                'animal' => null,
                'abbreviation' => 'ABBR',
            ]
        ];
    }