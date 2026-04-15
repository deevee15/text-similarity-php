<?php

    namespace PHPTextSimilarity\Config;

    class WeightConfig{
        public const proper = 2;
        public const names = 1.7;
        public const locations = 1.4;
        public const abbreviations = 2.5;
        public const organizations = 1.7;
        public const others = 0.3;

        public const similar_marker = 2;
        public const otherWords_marker = 1;
    }


