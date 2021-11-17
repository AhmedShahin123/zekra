<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Wehaa\CustomLinks\CustomLinks;

use Zekra\ShippingMatrix\ShippingMatrix;
use Zekra\Translations\Translations;
use Beyondcode\Reports\Reports;
use Beyondcode\Productprice\Productprice;
use App\Nova\Metrics\OrdersCount;
use Zekra\Settings\Settings;
use EricLagarda\NovaLinkResource\NovaLinkResource;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user->hasAnyRole(['super-admin','partner','courier']);
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new OrdersCount,
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
          (new CustomLinks)->canSee(function ($request) {
              return auth()->user()->hasRole('super-admin');
          }),
            (new ShippingMatrix)->canSee(function () {
                return auth()->user()->hasRole('courier') || auth()->user()->hasRole('super-admin');
            }),
            (new Translations)->canSee(function () {
                return auth()->user()->hasRole('super-admin');
            }),
            (new Settings)->canSee(function () {
                return auth()->user()->hasRole('super-admin');
            }),
            // (new NovaLinkResource())
            //     ->name('Transactions')
            //     ->to('/resources/transactions'),
            (new Productprice)->canSee(function () {
                return auth()->user()->hasRole('courier') || auth()->user()->hasRole('super-admin');
            }),
            \Vyuldashev\NovaPermission\NovaPermissionTool::make(),

        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
