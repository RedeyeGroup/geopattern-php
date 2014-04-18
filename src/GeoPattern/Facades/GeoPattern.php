<?php namespace GeoPattern\Facades;

use Illuminate\Support\Facades\Facade;

class GeoPattern extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'geopattern';
    }
}
