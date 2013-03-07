<?php

namespace imageColorTest\cases\core;

use imageColorTest\Unit;
use imageColorTest\stubs\core\ObjectStub;

class ObjectTest extends Unit
{
    public function testAutoConfigureSingleItem()
    {
        $foo = new ObjectStub(array(
            'foo' => 'elephpant',
        ));

        $this->assertSame('elephpant', $foo->foo);
    }

}
