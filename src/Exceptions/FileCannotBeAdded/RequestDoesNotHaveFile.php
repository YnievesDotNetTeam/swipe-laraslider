<?php

namespace YnievesDotNetTeam\SwipeLaraslider\Exceptions\FileCannotBeAdded;

use YnievesDotNetTeam\SwipeLaraslider\Helpers\EloquentHelpers;
use YnievesDotNetTeam\SwipeLaraslider\Exceptions\FileCannotBeAdded;

class RequestDoesNotHaveFile extends FileCannotBeAdded
{
    public static function create()
    {
        return new static("The current request does not have a file.");
    }
}
