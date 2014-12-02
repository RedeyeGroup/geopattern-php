<?php namespace GeoPattern\SVGElements;

class Path extends Base
{
    protected $tag = 'path';

    public function __construct($d, $args = array())
    {
        $this->elements = array(
            'd' => $d,
        );
        parent::__construct($args);
    }
}
