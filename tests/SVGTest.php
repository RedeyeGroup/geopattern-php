<?php
use RedeyeVentures\GeoPattern\SVG;

class SVGTest extends PHPUnit_Framework_TestCase
{
    protected $svg;

    protected function setUp()
    {
        $this->svg = new SVG();
    }

    public function testGetEmptySVGViaGetString()
    {
        $string = $this->svg->getString();
        $this->assertEquals($string, '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"></svg>', $string);
    }

    public function testGetEmptySVGViaCast()
    {
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"></svg>', $this->svg);
    }

    public function testSetSVGWidth()
    {
        $this->svg->setWidth(200);
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="200" height="100"></svg>', $this->svg);
    }

    public function testSetSVGHeight()
    {
        $this->svg->setHeight(200);
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="200"></svg>', $this->svg);
    }

    public function testSetSVGDimensionsChained()
    {
        $this->svg->setHeight(250)
            ->setWidth(150);
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="150" height="250"></svg>', $this->svg);
    }

    public function testAddRectangleBasic()
    {
        $this->svg->addRectangle(5, 10, 15, 20);
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect x="5" y="10" width="15" height="20" /></svg>', $this->svg);
    }

    public function testAddRectangleWithArg()
    {
        $this->svg->addRectangle(5, 10, 15, 20, array('fill' => 'rgb(25, 35, 45)'));
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect x="5" y="10" width="15" height="20" fill="rgb(25, 35, 45)" /></svg>', $this->svg);
    }

    public function testAddRectangleWithNestedArg()
    {
        $this->svg->addRectangle(5, 10, 15, 20, array('fill' => array('r' => 25, 'g' => 35, 'b' => 45)));
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect x="5" y="10" width="15" height="20" fill="r:25;g:35;b:45;" /></svg>', $this->svg);
    }

    public function testAddRectangleWithArgs()
    {
        $this->svg->addRectangle(5, 10, 15, 20, array('fill' => 'rgb(25, 35, 45)', 'xFill' => array('r' => 25, 'g' => 35, 'b' => 45)));
        $this->assertEquals('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect x="5" y="10" width="15" height="20" fill="rgb(25, 35, 45)" xFill="r:25;g:35;b:45;" /></svg>', $this->svg);
    }
}
