<?php

namespace imageColorTest\stubs;

use MischiefCollective\ColorJizz\Formats\RGB;
use MischiefCollective\ColorJizz\ColorJizz;

class ColorJizzStub extends RGB
{
    public $data = array();

    public function __construct() {}

    public function distance(ColorJizz $destinationColor)
    {
        return call_user_func($this->data['distance'], $destinationColor);
    }

}
