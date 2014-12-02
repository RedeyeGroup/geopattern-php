<?php namespace GeoPattern\SVGElements;

class Group extends Base
{
    protected $tag = 'g';
    protected $items;

    public function __construct($items = array(), $args = array())
    {
        $this->items = $items;
        $this->args = $args;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    public function getString()
    {
        $svgString = '';
        $svgString .= "<{$this->tag} {$this->argsToString($this->args)}>";
        foreach ($this->items as $item) {
            $svgString .= $item;
        }
        $svgString .= "</{$this->tag}>";

        return $svgString;
    }
}
