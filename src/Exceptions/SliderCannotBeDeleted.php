<?php

namespace YnievesDotNetTeam\SwipeLaraslider\Exceptions;

use Exception;
use YnievesDotNetTeam\SwipeLaraslider\Slider;

class SliderCannotBeDeleted extends Exception
{
   
    public function doesNotBelongToModel($id)
    {
        return new static("Slider with id '{$id}' does not Exists.");
    }
}
