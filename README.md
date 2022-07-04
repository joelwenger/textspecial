# TextSpecial

### Description

TextSpecial is a class for parsing a proprietary but simple markup language that can handle multiple columns. No CSS
framework was used, for simple fast and convenient use in smaller projects. 

### Requirements

- PHP >= 5.6

### Installation

```sh
composer require joelwenger/textspecial
```

### Basic Usage

first some settings:

```php
<?php

# use this namespace
use joelwenger\textspecial\src\TextSpecial;

# When installed via composer
require  __DIR__.'/vendor/autoload.php';

# Instantiate class
$textSpecial = new TextSpecial();

# multiple settings at once, not required
$textSpecial->setSettings( [
    'debug' => FALSE,
    'pathimg' => 'img/',
    'font_family' => 'Verdana,Arial,Helvetica,sans-serif',
    'color' => '#003388',
    'background' => '#ffffff',
    'table_title_bg_color' => '#888888',
    'table_title_text_color' => '#ffffff',
    'table_td_bg_color' => '#cccccc',
    'table_td_text_color' => '#000000',
    'code_text_color' => '#2e6e3c',
    'code_bg_color' => '#cccccc',
    'marking_color' => '#ff0000',
] );

// or single setting
$textSpecial->setSetting( 'color', '#003388');

// now read markup from file
$textSpecial->setFile( 'example.txt' );

// or get markup from string
$textSpecial->setText( 'c:9
h1u:title
p:Lorem ipsum dolor sit amet, ...' );

```

then the output:

```php
<!DOCTYPE html>

<html lang='de'>
<head>
    <title>any title</title>
    <meta http-equiv='content-type' content='text/html; charset=utf-8'>
    <style><?= $textSpecial->getCSS() ?></style>
</head>
<body>
<?= $textSpecial->getHtml() ?>
</body>
</html>

```
done


### Markup Language

As described in the example. Other options will certainly follow.

### @Todo

- Form creation
- using Bootstrap 5
- export as single file Html or MHTML
- parsing URL's, email addresses, etc.
- whatever ...

## License

TextSpecial is released under the MIT License.
