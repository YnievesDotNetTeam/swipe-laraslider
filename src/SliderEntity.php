<?php

namespace YnievesDotNetTeam\SwipeLaraslider;

use Illuminate\Database\Eloquent\Model;
use YnievesDotNetTeam\SwipeLaraslider\Slider;

class SliderEntity extends Model
{
    protected $table = 'slider_entities';
    protected $fillable = ['entity_id', 'entity_type', 'slider_id'];

    public function slider()
    {
        return $this->belongsTo(Slider::class, 'entity_id');
    }
}
