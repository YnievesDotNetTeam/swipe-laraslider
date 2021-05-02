# Swipe Laraslider

Image slider using Laravel and SwipeJS

## Following are the step to configure Image Slider

### Step 1:Laravel slider plugin requires the following components to work correctly

    Intervention Image

#### Step 2:copy vendor using composer

    composer require ynievesdotnetteam/swipe-laraslider dev-main

    Or, you may manually update require block and run `composer update`

    "require": {
       
        "ynievesdotnetteam/swipe-laraslider": "dev-main"
    }

    'composer update' will be required.

#### step 3: Once Swipe Laravel Slider is installed, You need to register the Service Provider in `config/app.php` Add following in `providers` list

    'providers' => [
     // ...
        YnievesDotNetTeam\SwipeLaraslider\ImageSliderServiceProvider::class,
     // ...

    ]

#### step 4: To publish the Config, Migration, Service Provider and Facades Run

    php artisan vendor:publish

#### step 5: Finally, run migration to generate table Run

    php artisan migrate

#### step 6: This packager Required Auth login if you don't have Auth login Run

    php artisan make:auth
    php artisan migrate

#### step 7: Add following link in your blade file for load CSS and Javasript

    <script src="{{ asset('vendor/assets/js/custome.js') }}"></script>
    <link href="{{ asset('vendor/assets/css/custome.css') }}" rel="stylesheet">

#### you can view laravel slider by writing:

localhost/yourapp/slider
