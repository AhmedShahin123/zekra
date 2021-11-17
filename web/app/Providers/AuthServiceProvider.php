<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\User'                       => 'App\Policies\UserPolicy',
        'Spatie\Permission\Models\Permission'   => 'App\Policies\PermissionPolicy',
        'Spatie\Permission\Models\Role'         => 'App\Policies\RolePolicy',
        'App\Models\Order'                      => 'App\Policies\OrderPolicy',
        'App\Models\Album'                      => 'App\Policies\AlbumPolicy',
        'App\Models\Image'                      => 'App\Policies\AlbumImagePolicy',
        'App\Models\Partner'                    => 'App\Policies\PartnerPolicy',
        'App\Models\City'                       => 'App\Policies\CityPolicy',
        'App\Models\Country'                    => 'App\Policies\CountryPolicy',
        'App\Models\Courier'                    => 'App\Policies\CourierPolicy',
        'App\Models\partnerOrder'               => 'App\Policies\PartnerOrderPolicy',
        'App\Models\courierOrder'               => 'App\Policies\CourierOrderPolicy',
        'App\Models\Language'                   => 'App\Policies\LanguagePolicy',
        'App\Models\Transaction'                => 'App\Policies\TransactionPolicy',
        'App\Models\Coupon'                     => 'App\Policies\CouponPolicy',
        'App\Models\CouponReport'               => 'App\Policies\CouponReportPolicy',
        'App\Models\Product'                    => 'App\Policies\ProductPolicy',
        'App\Models\Receipt'                    => 'App\Policies\ReceiptPolicy',
        'App\Models\Refund'                     => 'App\Policies\RefundPolicy',
        'App\Models\Email'                      => 'App\Policies\EmailPolicy',
        'App\Models\Payment'                    => 'App\Policies\PaymentPolicy',
        'App\Models\SalesReport'                => 'App\Policies\SalesReportPolicy'
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
