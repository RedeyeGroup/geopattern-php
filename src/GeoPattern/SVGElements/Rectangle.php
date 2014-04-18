<?php namespace GeoPattern\SVGElements;

class Rectangle extends Base
{
    protected $tag = 'rect';

    public function __construct($x, $y, $width, $height, $args = array())
    {
        $this->elements = array(
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        );
        parent::__construct($args);
    }
}
