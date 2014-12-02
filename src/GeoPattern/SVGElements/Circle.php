<?php namespace GeoPattern\SVGElements;

class Circle extends Base
{
    protected $tag = 'circle';

    public function __construct($cx, $cy, $r, $args = array())
    {
        $this->elements = array(
            'cx' => $cx,
            'cy' => $cy,
            'r' => $r,
        );
        parent::__construct($args);
    }
}
