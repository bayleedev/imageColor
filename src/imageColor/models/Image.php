<?php

namespace imageColor\models;

use InvalidArgumentException;
use imageColor\core\Object;

class Image extends Object
{
    /**
     * Image resource to wrap.
     *
     * @var resource
     */
    protected $_file = null;

    /**
     * Cached information about the file.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Minimum distance colors should be count as a "match".
     *
     * The lower the number, the more precise the scale is.
     *
     * @var int
     */
    protected $_threshold = 30;

    /**
     * Percent of pixels to analyze.
     *
     * The lower the number the faster the image will process.
     *
     * @var int
     */
    protected $_precision = 30;

    /**
     * Map of file types to image create types.
     *
     * @var array
     */
    protected $_fileTypes = array(
        'png' => 'imagecreatefrompng',
        'bmp' => 'imagecreatefromwbmp',
        'gif' => 'imagecreatefromgif',
        'jpg' => 'imagecreatefromjpeg',
        'jpeg' => 'imagecreatefromjpeg',
    );

    /**
     * Static dependencies.
     *
     * @var array
     */
    protected $_classes = array(
        'color' => 'imageColor\util\Color',
    );

    /**
     * Holds an array of values that should be processed on initialization.
     *      - `'classes'`: Static dependencies.
     *      - `'precision'`: Percentage of pixels analyzed.
     *      - `'fileTypes'`: Image to resource functions.
     *      - `'threshold'`: Minimum threshold of a color.
     * @var array
     */
    protected $_autoConfig = array('classes', 'precision', 'fileTypes', 'threshold');

    /**
     * @throws InvalidArgumentException If the image path cannot be found or
     *                                   you did not provide a valid resource.
     * @param  mixed    $file Path to the image, or a valid GD resource.
     * @return Resource
     */
    public function load($file)
    {
        if (is_string($file)) {
            if (!file_exists($file)) {
                throw new InvalidArgumentException("Image not found: {$file}");
            }
            $info = pathinfo($file);
            if (!isset($this->_fileTypes[$info['extension']])) {
                throw new InvalidArgumentException("Image not supported: {$info['extension']}");
            }

            return $this->_file = call_user_func($this->_fileTypes[$info['extension']], $file);
        }
        if (is_resource($file)) {
            return $this->_file = $file;
        }
        throw new InvalidArgumentException('Resource or image path not given.');
    }

    /**
     * A getter/setter for data about the `Image`.
     *
     * {{{
     * $image = new Image();
     * $iamge->load(__DIR__ . '/flowers.png');
     * $image->data('width'); // 104
     * $image->data('width', 96); // 96
     * $image->data('width'); // 96
     * }}}
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function data($key, $value = null)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        if (!is_null($value)) {
            return $this->_data[$key] = $value;
        }
        switch ($key) {
            case 'width':
                return $this->data($key, imagesx($this->_file));
            case 'height':
                return $this->data($key, imagesy($this->_file));
        }

        return null;
    }

    /**
     * Will return a the top colors on the image.
     *
     * Iterates over each pixel, determining it's closest color.
     *
     * {{{
     * $image = new Image();
     * $iamge->load(__DIR__ . '/flowers.png');
     * $image->primaryColors(3); // pink, light-red, dark-green
     * }}}
     *
     * @param  int   $count Number of top items to return, defaults to all.
     * @return array Will contain 1+ main colors on this image.
     */
    public function primaryColors($count = 0)
    {
        if (!empty($this->primaryColors)) {
            if (empty($count)) {
                return $this->primaryColors;
            }

            return array_slice($this->primaryColors, 0, $count);
        }
        $colorPoll = array();
        $color = $this->_classes['color'];
        for ($x = 0, $width = $this->data('width'); $x < $width; $x++) {
            for ($y = 0, $height = $this->data('height'); $y < $height; $y++) {
                if (mt_rand(0, 99) > $this->_precision) {
                    continue;
                }
                $key = $color::bestColor($color::colorAt($this->_file, $x, $y), array(
                    'threshold' => $this->_threshold,
                ));
                if ($key === false) {
                    continue;
                }
                $colorPoll[$key] = isset($colorPoll[$key]) ? $colorPoll[$key] + 1 : 1;
            }
        }
        arsort($colorPoll);
        $this->primaryColors = $colorPoll;

        return $this->primaryColors($count);
    }

    /**
     * Provides a simple syntax for making assertions about the properties of an `Image`.
     *
     * {{{
     * $image = new Image();
     * $iamge->load(__DIR__ . '/flowers.png');
     * $image->is('light'); // true
     * }}}
     *
     * @param string $flag Flag you wish to assert.
     *                       - `'dark'`: If there are more black values than white (darker image).
     *                       - `'black'`: Same as `dark`.
     *                       - `'light'`: If there are more white values than black (lighter image).
     *                       - `'white'`: Same as `light`.
     * @return boolean
     */
    public function is($flag)
    {
        $color = $this->_classes['color'];
        switch ($flag) {
            case 'dark':
            case 'black':
                $primaryColors = $this->primaryColors();
                $colors = $color::colors();
                $totalColors = array_sum($primaryColors);
                $totalLightness = 0;
                foreach ($primaryColors as $colorKey => $count) {
                    $totalLightness += $count * ($colors[$colorKey]->lightness);
                }

                return ($totalLightness / $totalColors) < 50;
            case 'light':
            case 'white':
                return !$this->is('dark');
        }

        return false;
    }

}
