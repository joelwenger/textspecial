<?php

namespace joelwenger\textspecial\example;

use joelwenger\textspecial\src\TextSpecial;

// autoloader
require realpath( __DIR__.'/../vendor/autoload.php' );

// Instantiate class
$textSpecial = new TextSpecial();

// multiple settings at once
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

// read markup from file
$textSpecial->setFile( 'example.txt' );
?>
<!DOCTYPE html>

<html lang='de'>
<head>
    <title>TextSpecial Example</title>
    <meta http-equiv='content-type' content='text/html; charset=utf-8'>
    <style><?= $textSpecial->getCSS() ?></style>
</head>
<body>
<?= $textSpecial->getHtml() ?>
</body>
</html>
