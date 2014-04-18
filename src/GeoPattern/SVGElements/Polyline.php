<?php namespace GeoPattern\SVGElements;

class Polyline extends Base
{
    protected $tag = 'polyline';

    public function __construct($points, $args = array())
    {
        $this->elements = array(
            'points' => $points,
        );
        parent::__construct($args);
    }
}
