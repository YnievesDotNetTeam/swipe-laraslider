<?php

namespace YnievesDotNetTeam\SwipeLaraslider;

use Illuminate\Support\ServiceProvider;
use YnievesDotNetTeam\SwipeLaraslider\Classes\SliderClass;
use YnievesDotNetTeam\SwipeLaraslider\Controller\SliderController;

class ImageSliderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Config
        $this->publishes([__DIR__.'/../config/slider.php' => config_path('slider.php')]);

        // Migration
        $this->publishes([__DIR__.'/../database/migrations' => $this->app->databasePath().'/migrations'], 'migrations');

        //access Routes
        include __DIR__.'/Routes/web.php';

        // to Publish assets Folder

        $this->publishes([__DIR__.'/Resources/assets' => public_path('vendor/assets'),
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('slider', function () {
            return new SliderController();
        });

        $this->app->make('YnievesDotNetTeam\SwipeLaraslider\Controller\SliderController');
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'swipe-laraslider');
    }
}
