<?php

namespace imageColorTest\cases\models;

use org\bovigo\vfs\vfsStream;
use imageColor\models\Image;
use imageColorTest\stubs\util\ColorStub;
use imageColorTest\stubs\ColorJizzStub;
use imageColorTest\Unit;

class ImageTest extends Unit
{
    public function setUp()
    {
        ColorStub::$data = array();
    }

    public function createImage($image, $path)
    {
        $fileSystem = $this->setupWebroot();

        ob_start();
        imagepng($image);
        vfsStream::newFile(basename($path))
            ->withContent(ob_get_contents())
            ->at($fileSystem->getChild('img'));
        ob_end_clean();

        return $fileSystem;
    }

    public function setupWebroot()
    {
        return vfsStream::setup('webroot', 755, array(
            'img' => array(),
        ));
    }

    public function setupBasicImageSetup($options)
    {
        $options += array(
            'precision' => 30,
            'classes' => array(
                'color' => 'imageColorTest\stubs\util\ColorStub',
            ),
        );
        $image = new Image($options);
        $image->data('width', 10);
        $image->data('height', 10);
        ColorStub::$data['bestColor'] = function($color, $options) {
            return 'pink';
        };
        ColorStub::$data['colorAt'] = function($img, $x, $y) {
            return new ColorJizzStub(255, 255, 255);
        };

        return $image;
    }

    public function testLoadWithoutFileOrResource()
    {
        $this->setExpectedException('InvalidArgumentException');

        $image = new Image();

        $image->load(true);
    }

    public function testLoadInValidFile()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->setupWebroot();
        $image = new Image();

        $image->load(vfsStream::url('webroot/img/flower.png'));
    }

    public function testLoadInValidFileType()
    {
        $this->setExpectedException('InvalidArgumentException');

        vfsStream::setup('webroot', null, array(
            'img' => array(
                'flower.foo' => 'foo',
            ),
        ));
        $image = new Image();

        $image->load(vfsStream::url('webroot/img/flower.foo'));
    }

    public function testCanSaveImagetoVfsStream()
    {
        $path = vfsStream::url('webroot/img/flower.png');
        $img = imagecreate(1, 1);
        $black = imagecolorallocate($img, 0, 0, 0);
        $this->createImage($img, $path);
        $image = new Image();

        $this->assertInternalType('resource', $image->load($path));
    }

    public function testLoadWithResource()
    {
        $img = imagecreate(1, 1);
        $image = new Image();

        $this->assertSame($img,  $image->load($img));
    }

    public function testDataReturnsNullWhenNotFoundOrDefaultKeys()
    {
        $image = new Image();

        $this->assertNull($image->data('foo'));
    }

    public function testDataReturnsValueWhenSet()
    {
        $image = new Image();
        $image->data('foo', 'bar');

        $this->assertSame('bar', $image->data('foo'));
    }

    public function testDataReturnsCorrectDimensions()
    {
        $path = vfsStream::url('webroot/img/flower.png');
        $img = imagecreate(10, 5);
        $black = imagecolorallocate($img, 0, 0, 0);
        $this->createImage($img, $path);
        $image = new Image();
        $image->load($path);

        $this->assertSame(10, $image->data('width'));
        $this->assertSame(5, $image->data('height'));
    }

    public function testDataReturnsOverwrittenDimensions()
    {
        $path = vfsStream::url('webroot/img/flower.png');
        $img = imagecreate(10, 5);
        $black = imagecolorallocate($img, 0, 0, 0);
        $this->createImage($img, $path);
        $image = new Image();
        $image->load($path);

        $image->data('width', 20);
        $image->data('height', 40);

        $this->assertSame(20, $image->data('width'));
        $this->assertSame(40, $image->data('height'));
    }

    public function testIsWithDarkImage()
    {
        $image = new Image(array(
            'classes' => array(
                'color' => 'imageColorTest\stubs\util\ColorStub',
            ),
        ));
        $image->primaryColors = array(
            'red' => 10,
        );
        ColorStub::$data['colors'] = function() {
            return array(
                'red' => (object) array(
                    'lightness' => 40,
                ),
            );
        };

        $this->assertTrue($image->is('black'));
        $this->assertTrue($image->is('dark'));
        $this->assertFalse($image->is('white'));
        $this->assertFalse($image->is('light'));
    }

    public function testIsWithLightImage()
    {
        $image = new Image(array(
            'classes' => array(
                'color' => 'imageColorTest\stubs\util\ColorStub',
            ),
        ));
        $image->primaryColors = array(
            'red' => 10,
        );
        ColorStub::$data['colors'] = function() {
            return array(
                'red' => (object) array(
                    'lightness' => 80,
                ),
            );
        };

        $this->assertFalse($image->is('black'));
        $this->assertFalse($image->is('dark'));
        $this->assertTrue($image->is('white'));
        $this->assertTrue($image->is('light'));
    }

    public function testIsWithLightImageComplex()
    {
        $image = new Image(array(
            'classes' => array(
                'color' => 'imageColorTest\stubs\util\ColorStub',
            ),
        ));
        $image->primaryColors = array(
            'red' => 20,
            'yellow' => 30,
            'blue' => 30,
        );
        ColorStub::$data['colors'] = function() {
            return array(
                'red' => (object) array(
                    'lightness' => 80,
                ),
                'yellow' => (object) array(
                    'lightness' => 20,
                ),
                'blue' => (object) array(
                    'lightness' => 0,
                ),
            );
        };

        $this->assertTrue($image->is('black'));
        $this->assertTrue($image->is('dark'));
        $this->assertFalse($image->is('white'));
        $this->assertFalse($image->is('light'));
    }

    public function testReturnsSpecificPrimaryColors()
    {
        $image = new Image();
        $image->primaryColors = array(
            'black' => 1,
            'white' => 1,
            'blue' => 1,
            'red' => 1,
            'green' => 1,
        );

        $this->assertCount(1, $image->primaryColors(1));
        $this->assertCount(3, $image->primaryColors(3));
    }

    public function testReturnsAllPrimaryColors()
    {
        $image = new Image();
        $image->primaryColors = array(
            'black' => 1,
            'white' => 1,
            'blue' => 1,
            'red' => 1,
            'green' => 1,
        );

        $this->assertCount(5, $image->primaryColors(0));
        $this->assertCount(5, $image->primaryColors());
    }

    public function testPrimaryColorsReturns100PercentOfStaticColor()
    {
        $image = $this->setupBasicImageSetup(array(
            'precision' => 100,
        ));

        $colors = $image->primaryColors();
        $this->assertSame(100, $colors['pink']);
    }

    public function testPrimaryColorsReturnsNot100PercentOfStaticColor()
    {
        $image = $this->setupBasicImageSetup(array(
            'precision' => 30,
        ));

        $colors = $image->primaryColors();
        $this->assertNotSame(100, $colors['pink']);
    }

    public function testPrimaryColorsReturnsCorrectColorKeys()
    {
        $image = $this->setupBasicImageSetup(array(
            'precision' => 100,
        ));
        $i = 0;
        ColorStub::$data['bestColor'] = function($color, $options) use (&$i) {
            $i++;
            if ($i > 10) {
                return 'blue';
            }

            return 'pink';
        };

        $colors = $image->primaryColors();
        $this->assertArrayHasKey('pink', $colors);
        $this->assertArrayHasKey('blue', $colors);
    }

    public function testPrimaryColorsReturnsCorrectColorKeyValues()
    {
        $image = $this->setupBasicImageSetup(array(
            'precision' => 100,
        ));
        $i = 0;
        ColorStub::$data['bestColor'] = function($color, $options) use (&$i) {
            $i++;
            if ($i > 10) {
                return 'blue';
            }

            return 'pink';
        };

        $colors = $image->primaryColors();
        $this->assertSame(10, $colors['pink']);
        $this->assertSame(90, $colors['blue']);
    }

    public function testPrimaryColorsReturnsCorrectOrderedColors()
    {
        $image = $this->setupBasicImageSetup(array(
            'precision' => 100,
        ));
        $i = 0;
        ColorStub::$data['bestColor'] = function($color, $options) use (&$i) {
            $i++;
            if ($i > 10) {
                return 'blue';
            }

            return 'pink';
        };

        $colors = $image->primaryColors();
        $this->assertSame(array('blue', 'pink'), array_keys($colors));
    }
}
