<?php

namespace imageColorTest\stubs\core;

use imageColor\core\Object;

class ObjectStub extends Object
{
    protected $_autoConfig = array('foo', 'bar', 'baz');

    public function __get($key)
    {
        if (isset($this->{"_$key"})) {
            return $this->{"_$key"};
        }

        return;
    }

}
