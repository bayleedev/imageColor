<?php

namespace imageColorTest\stubs\util;

use imageColor\util\Color;
use MischiefCollective\ColorJizz\ColorJizz;

class ColorStub extends Color
{
    public static $data = array();

    public static function colorAt($img, $x, $y)
    {
        if (isset(static::$data['colorAt'])) {
            return call_user_func(static::$data['colorAt'], $img, $x, $y);
        }

        return parent::colorAt($img, $x, $y);
    }

    public static function bestColor(ColorJizz $color, array $options = array())
    {
        if (isset(static::$data['bestColor'])) {
            return call_user_func(static::$data['bestColor'], $color, $options);
        }

        return parent::bestColor($color, $options);
    }

    public static function colors(array $color = null)
    {
        if (isset(static::$data['colors'])) {
            return call_user_func(static::$data['colors'], $color);
        }

        return parent::colors($color);
    }

}
