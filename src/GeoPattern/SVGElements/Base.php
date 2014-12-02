<?php namespace GeoPattern\SVGElements;

abstract class Base
{
    protected $tag;
    protected $elements;
    protected $args;

    public function __construct($args)
    {
        $this->args = $args;
    }

    public function elementsToString()
    {
        $string = ' ';
        foreach ($this->elements as $key => $value) {
            $string .= "$key=\"$value\" ";
        }
        return $string;
    }

    public function argsToString()
    {
        $string = '';
        foreach ($this->args as $key => $value) {
            if (is_array($value)) {
                $string .= "$key=\"";
                foreach ($value as $k => $v) {
                    $string .= "$k:$v;";
                }
                $string .= '" ';
            } else {
                $string .= "$key=\"$value\" ";
            }
        }
        return $string;
    }

    public function getString()
    {
        return "<{$this->tag}{$this->elementsToString()}{$this->argsToString()}/>";
    }

    public function __toString()
    {
        return $this->getString();
    }
}
