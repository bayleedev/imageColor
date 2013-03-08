## Image Color

Helps grab primary color information from photos. This was originally created to put similarly colored images together, similiar to the functionality of etsy's homepage.

[![Build Status](https://secure.travis-ci.org/BlaineSch/imageColor.png?branch=master)](http://travis-ci.org/BlaineSch/imageColor)

## Installation

### GIT
~~~ bash
git clone git://github.com/BlaineSch/imageColor.git imageColor
cd imageColor && php composer.phar install
~~~

### Composer
~~~ json
"require": {
    "blainesch/imagecolor": "1.0.0"
}
~~~
~~~ bash
php compooser.phar install
~~~

## Running Tests
You'll need to install with dev dependencies, then run phpunit.
~~~ bash
git clone git://github.com/BlaineSch/imageColor.git imageColor
cd imageColor && php composer.phar install --dev
phpunit
~~~

## Usage

### Script
~~~ php
<?php
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
?>
~~~

### Output
~~~ bash
Array
(
    [ruby.jpg] => Array
        (
            [gray] => 9631
            [red] => 8687
            [gray-purple] => 8214
            [light-gray] => 6045
            [black] => 4497
            [light-red] => 4053
            [dark-red] => 3135
            [brown] => 2072
            [maroon] => 1241
            [light-brown] => 950
            [tan] => 746
            [white] => 667
            [light-pink] => 610
            [pink] => 343
            [dark-orange] => 341
            [light-blue] => 115
        )

    [php.jpg] => Array
        (
            [gray-purple] => 25619
            [sky-blue] => 11809
            [black] => 8190
            [blue] => 2895
            [light-blue] => 769
            [blue-purple] => 351
            [light-gray] => 313
            [gray] => 228
            [white] => 157
            [brown] => 29
            [baby-blue] => 7
            [tan] => 5
        )

)
~~~