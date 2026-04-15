# TextSimilarity

TextSimilarity is pure PHP library for detecting duplicate news articles using morphological 
analysis and entity-weighted scoring.

Read more about TextSimilarity:
- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Getting started](#getting-started)
- [How it works](#how-it-works)
- [Demo](#demo)
- [Licence](#license)

## Features
- Morphological text processing via phpMorphy
- Named entity extraction (names, locations, organizations, abbreviations)
- Weighted scoring system with configurable coefficients
- File-based word cache for performance
- English and Russian language support

## Installation

TextSimilarity is installed via [Composer](https://getcomposer.org/).
To [add a dependency](https://getcomposer.org/doc/04-schema.md#package-links) to TextSimilarity in your project,

Run the following to use the latest stable version
```sh
composer require deevee15/text-similarity-php
```

## Requirements

- PHP 8.1 or above
- [phpMorphy library](https://github.com/cijic/phpmorphy)

## Getting started
```php
use PHPTextSimilarity\TextSimilarity;

$result = TextSimilarity::compare(
    'en',
    'First article`s text...',
    'Second article`s text...',
    ['first' => "Article's title", 'second' => "Article's title"]
);
```

## How it works

The TextSimilarity library divides all words from compared texts and article titles into entities (proper names, common nouns, locations, abbreviations, organizations), converts them to the nominative case, then retains only the matching ones, assigns points based on the matches, and multiplies them by the importance coefficients specified in src/Config/WeightConfig.php.

## Demo
Here is the link to the [demo website](https://similarity.deevee.ru)

## License
Apache 2.0