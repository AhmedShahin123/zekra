<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DependenciesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // bind abstract repo interface with concrate repo class
        $this->app->bind(
            "App\Repositories\User\UserRepositoryInterface",
            "App\Repositories\User\UserRepository"
        );

        $this->app->bind(
            "App\Repositories\Country\CountryRepositoryInterface",
            "App\Repositories\Country\CountryRepository"
        );

        $this->app->bind(
            "App\Repositories\City\CityRepositoryInterface",
            "App\Repositories\City\CityRepository"
        );

        $this->app->bind(
            "App\Repositories\Order\OrderRepositoryInterface",
            "App\Repositories\Order\OrderRepository"
        );

        $this->app->bind(
            "App\Repositories\Album\AlbumRepositoryInterface",
            "App\Repositories\Album\AlbumRepository"
        );

        $this->app->bind(
            "App\Repositories\Image\ImageRepositoryInterface",
            "App\Repositories\Image\ImageRepository"
        );

        // bind abstract service interface with concrate service class
        $this->app->bind(
            "App\Services\User\UserServiceInterface",
            "App\Services\User\UserService"
        );

        $this->app->bind(
            "App\Services\Country\CountryServiceInterface",
            "App\Services\Country\CountryService"
        );

        $this->app->bind(
            "App\Services\City\CityServiceInterface",
            "App\Services\City\CityService"
        );

        $this->app->bind(
            "App\Services\Order\OrderServiceInterface",
            "App\Services\Order\OrderService"
        );

        $this->app->bind(
            "App\Services\Album\AlbumServiceInterface",
            "App\Services\Album\AlbumService"
        );

        $this->app->bind(
            "App\Services\Image\ImageServiceInterface",
            "App\Services\Image\ImageService"
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
