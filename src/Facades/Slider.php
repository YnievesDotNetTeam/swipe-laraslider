<?php

namespace YnievesDotNetTeam\SwipeLaraslider\Facades;

use Illuminate\Support\Facades\Facade;

class Slider extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'slider';
    }
}
