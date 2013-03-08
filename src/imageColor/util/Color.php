<?php

namespace imageColor\util;

use MischiefCollective\ColorJizz\Formats\RGB;
use MischiefCollective\ColorJizz\Formats\Hex;
use MischiefCollective\ColorJizz\ColorJizz;

class Color
{
    /**
     * Map of predefined colors to match against.
     *
     * These will be convted to the Color model at runtime.
     *
     * @var  array
     */
    protected static $_colors = array(
        'black' => 0x000000,
        'white' => 0xFFFFFF,
        'gray' => 0x999999,
        'light-gray' => 0xCCCCCC,
        'maroon' => 0x620804,
        'red' => 0xC41C0E,
        'light-red' => 0xC43431,
        'dark-red' => 0x931208,
        'orange' => 0xf66914,
        'dark-orange' => 0xC66826,
        'light-orange' => 0xf89b11,
        'brown' => 0x633303,
        'light-brown' => 0x966829,
        'tan' => 0xE6d9AC,
        'yellow' => 0xFBCF0C,
        'light-yellow' => 0xF3E107,
        'sb-yellow' => 0xFEFE04,
        'olive' => 0x999B00,
        'dark-olive' => 0x676800,
        'light-olive' => 0xCCCF02,
        'green' => 0x6C9C00,
        'dark-green' => 0x396800,
        'lime' => 0x80CF00,
        'blue' => 0x1F5FD3,
        'baby-blue' => 0x74CCCD,
        'sky-blue' => 0x2E96D1,
        'light-blue' => 0xACBBDD,
        'purple' => 0x632A9F,
        'blue-purple' => 0x322A9F,
        'gray-purple' => 0x424055,
        'voilet' => 0x932C9F,
        'pink' => 0xE14A8B,
        'light-pink' => 0xF8ACC9,
    );

    /**
     * Gives the RGB color at a given point on the current image.
     *
     * {{{
     * use imageColor\util\Color;
     *
     * $image = imagecreatefrompng("flowers.png");
     * echo Color::colorAt($image, 10, 10)->toRGB()->toString(); // 255,165,210
     * }}}
     *
     * @param  resource $img
     * @param  int      $x
     * @param  int      $y
     * @return CIELab   Object
     */
    public static function colorAt($img, $x, $y)
    {
        $rgba = imagecolorsforindex($img, imagecolorat($img, $x, $y));
        $rgb = new RGB($rgba['red'], $rgba['green'], $rgba['blue']);

        return $rgb->toCIELab();
    }

    /**
     * Given a color, we determine which predefined color in `static::$_colors`best match.
     *
     * {{{
     * use imageColor\util\Color;
     * use MischiefCollective\ColorJizz\Formats\RGB;
     *
     * $red = new RGB(255, 128, 128);
     * var_dump(Color::bestColor($red)); // light-red
     * }}}
     *
     * @param Color $color
     * @param array $options Array of optinos to overwrite:
     *                         - `'threshold'`: Set minimum threshold for matching.
     * @return string|false If no numbers are below the threshold, false will be returned.
     */
    public static function bestColor(ColorJizz $color, array $options = array())
    {
        $options += array(
            'threshold' => 30,
        );
        $min = 9999;
        $bestColor = null;
        static::updateColors();
        foreach (static::$_colors as $key => $value) {
            $tmin = $color->distance($value);
            if ($min > $tmin) {
                $bestColor = $key;
                $min = $tmin;
            }
        }

        return $min < $options['threshold'] ? $bestColor : false;
    }

    /**
     * Will convert the predefined HEX colors to `ColorJizz` objects.
     *
     * @return void
     */
    protected static function updateColors()
    {
        $keys = array_keys(static::$_colors);
        if (is_object(static::$_colors[$keys[0]])) {
            return;
        }
        foreach (static::$_colors as $color => $value) {
            static::$_colors[$color] = new Hex($value);
            static::$_colors[$color] = static::$_colors[$color]->toCIELab();
        }

        return;
    }

    /**
     * Will return the current colors, which can be overwritten with this method.
     *
     * @param  array $colors
     * @return array
     */
    public static function colors(array $colors = null)
    {
        if (!is_null($colors)) {
            static::$_colors = $colors;
        }
        static::updateColors();

        return static::$_colors;
    }

}
