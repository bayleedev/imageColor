<?php
// Autoloader
require_once __DIR__ . '/../tests/bootstrap.php';

// Script
use imageColor\models\Image;

$elephpant = new Image();
$elephpant->load(__DIR__ . '/php.jpg');
$elephpantColors = $elephpant->primaryColors();

$rubyShirt = new Image();
$rubyShirt->load(__DIR__ . '/ruby.jpg');
$rubyShirtColors = $rubyShirt->primaryColors();

print_r(array(
    'ruby.jpg' => $rubyShirtColors,
    'php.jpg' => $elephpantColors,
));
