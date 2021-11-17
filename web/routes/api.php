<?php

use App\Models\Album;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['cors', 'locale']], function () {

    // Version 1 Routes
    Route::group(['namespace' => 'API\V1', 'prefix' => 'v1'], function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');

        Route::post('auth/facebook', 'SocialAuthController@facebookAuth');

        Route::get('login/{provider}', 'AuthController@redirectToProvider');
        Route::get('login/{provider}/callback', 'AuthController@handleProviderCallback');

        Route::get('countries', 'CountriesController@getCountries');


        Route::post('courierShipping', 'OrdersController@courierShipping');
        Route::post('partnerTax', 'OrdersController@partnerTax');

        Route::post('stripe', 'StripePaymentController@stripePost');

        Route::get('languages', 'LanguagesController@getLanguages');

        Route::get('cities', 'CitiesController@getCities');
        Route::get('cities/{id}', 'CitiesController@getCity');
        Route::post('countryCities', 'CitiesController@countryCities');
        Route::post('createReceipt', 'OrdersController@createReceipt');

        Route::get('packages', 'PackagesController@getPackages');

        Route::group(['middleware'=>'auth:api'], function () {
            Route::post('addOrder', 'OrdersController@addOrder');
            Route::get('myOrders', 'OrdersController@myOrders');
            Route::post('orderDetails', 'OrdersController@orderDetails');
            Route::post('orders/{id}/checkout', 'OrdersController@checkoutOrder');

            Route::get('coupons/{code}', 'CouponsController@getCoupon');

            Route::post('packages/{id}/purchase', 'PackagesController@purchasePackage');

            Route::group(['prefix' => 'user'], function () {
                Route::put('password', 'UserController@changePassword');
                Route::put('profile', 'UserController@changeProfile');
                Route::get('profile', 'UserController@getProfile');

                Route::post('reOrder', 'OrdersController@reOrder');

                Route::post('addresses', 'AddressesController@createAddress');
                Route::get('addresses', 'AddressesController@getAddresses');
                Route::delete('addresses/{id}', 'AddressesController@deleteAddress');

                Route::get('cards', 'PaymentsController@getCards');
                Route::post('cards', 'PaymentsController@createCard');
                Route::delete('cards/{card_id}', 'PaymentsController@deleteCard');
                Route::put('cards/{card_id}/default', 'PaymentsController@defaultCard');

                // refund request
                Route::post('orders/{id}/refund', 'OrdersController@refundOrder');
            });
        });
    });

    // Version 2 Routes
    Route::group(['namespace' => 'API\V2', 'prefix' => 'v2'], function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::post('sendCode', 'AuthController@sendCode');
        Route::post('checkCode', 'AuthController@checkCode');
        Route::post('updatePassword', 'AuthController@updatePassword');

        Route::post('auth/facebook', 'SocialAuthController@facebookAuth');

        Route::get('login/{provider}', 'AuthController@redirectToProvider');
        Route::get('login/{provider}/callback', 'AuthController@handleProviderCallback');

        Route::get('countries', 'CountriesController@getCountries');


        Route::post('courierShipping', 'OrdersController@courierShipping');
        Route::post('partnerTax', 'OrdersController@partnerTax');

        Route::post('stripe', 'StripePaymentController@stripePost');

        Route::get('languages', 'LanguagesController@getLanguages');

        Route::get('cities', 'CitiesController@getCities');
        Route::get('cities/{id}', 'CitiesController@getCity');
        Route::post('countryCities', 'CitiesController@countryCities');
        Route::post('createReceipt', 'OrdersController@createReceipt');

        Route::get('packages', 'PackagesController@getPackages');

        Route::group(['middleware'=>'auth:api'], function () {

            // Orders Routes
            Route::post('addOrder', 'OrdersController@addOrder');
            Route::get('myOrders', 'OrdersController@myOrders');
            Route::post('orderDetails', 'OrdersController@orderDetails');
            Route::post('orders/{id}/checkout', 'OrdersController@checkoutOrder');

            Route::post('orders', 'OrdersController@createOrder');
            Route::get('orders/price', 'OrdersController@getPriceVariables');
            Route::put('orders/{id}/pay', 'OrdersController@payOrder');
            Route::post('orders/{id}/albums/{image_format}', 'OrdersController@createAlbum')->where('image_format', '(base64|multipart)');

            Route::get('coupons/{code}', 'CouponsController@getCoupon');

            Route::post('packages/{id}/purchase', 'PackagesController@purchasePackage');

            Route::group(['prefix' => 'user'], function () {
                Route::put('password', 'UserController@changePassword');
                Route::put('profile', 'UserController@changeProfile');
                Route::get('profile', 'UserController@getProfile');

                Route::post('reOrder', 'OrdersController@reOrder');

                Route::post('addresses', 'AddressesController@createAddress');
                Route::get('addresses', 'AddressesController@getAddresses');
                Route::put('addresses/{id}', 'AddressesController@updateAddress');
                Route::put('addresses/{id}/default', 'AddressesController@defaultAddress');
                Route::delete('addresses/{id}', 'AddressesController@deleteAddress');



                Route::get('cards', 'PaymentsController@getCards');
                Route::post('cards', 'PaymentsController@createCard');
                Route::delete('cards/{card_id}', 'PaymentsController@deleteCard');
                Route::put('cards/{card_id}/default', 'PaymentsController@defaultCard');

                // refund request
                Route::post('orders/{id}/refund', 'OrdersController@refundOrder');
            });
        });
    });
});
