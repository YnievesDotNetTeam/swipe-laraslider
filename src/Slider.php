<?php

namespace YnievesDotNetTeam\SwipeLaraslider;

use Illuminate\Database\Eloquent\Model;
use YnievesDotNetTeam\SwipeLaraslider\SliderImage;

class Slider extends Model
{
    protected $table = 'sliders';

    protected $fillable = ['name', 'slider_type', 'auto_play', 'slides_per_page', 'slider_height', 'slider_width' , 'is_active'];


    public function slides()
    {
        return $this->hasMany(SliderImage::class);
    }
}
