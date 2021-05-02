<?php

namespace YnievesDotNetTeam\SwipeLaraslider;

use Illuminate\Database\Eloquent\Model;
use YnievesDotNetTeam\SwipeLaraslider\Slider;

class SliderImage extends Model
{
    protected $table = 'slider_images';

    protected $fillable = ['slider_id', 'title', 'is_active', 'caption', 'description', 'image_name', 'start_date', 'end_date'];

    public function slider()
    {
        return $this->belongsTo(Slider::class, 'slider_id');
    }
}
