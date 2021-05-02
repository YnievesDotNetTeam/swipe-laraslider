<?php

Route::post('slides/preview', 'YnievesDotNetTeam\SwipeLaraslider\Controller\SliderController@preview');
Route::get('slider/changeStatus/{id}', 'YnievesDotNetTeam\SwipeLaraslider\Controller\SliderController@changeSliderStatus');
Route::resource('slider', 'YnievesDotNetTeam\SwipeLaraslider\Controller\SliderController');
