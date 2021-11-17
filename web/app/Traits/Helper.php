<?php

namespace App\Traits;

use App\Models\Coupon;
use App\Models\Currency;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;

trait Helper {

    public function validateCoupon(Coupon $coupon){
        // return $validationStatus;
        // get the type of coupon
        // check if the coupon is active
        // if it is a discount coupon => check for expiration date
        // if it is an invitation coupon => check for times of usage, and if the user has used this card before

        // get all the orders that have used this coupon before
        $orders = $coupon->orders;
        $usersEmails = $orders->pluck('user')->pluck('email')->toArray();

        $validationStatus = ['failed' => false, 'message' => ''];
        $type = $coupon->type;

        if(!$coupon->active){
            $validationStatus['failed'] = true;
            $validationStatus['message'] = 'This code has been disabled';
        }

        elseif(in_array(auth()->user()->email, $usersEmails)){
            // this user has used this coupon before
            $validationStatus['failed'] = true;
            $validationStatus['message'] = 'This user has used this code before';
        }

        elseif(count($orders) >= $coupon->usage_times){
            // this code has exceeded its usage limit
            $validationStatus['failed'] = true;
            $validationStatus['message'] = 'This code has exceeded its usage limit';
        }

        elseif($type == 'discount'){
            $expire_date = Carbon::parse($coupon->expire_at);
            $today = Carbon::today();
            
            $validationStatus['failed'] = $expire_date->lessThanOrEqualTo($today);
            $validationStatus['message'] = 'This code has expired';
        }

        return $validationStatus;
    } 

    public function getLocalMoney($amount){
        $defaultCurrency = 'USD';
        $defaultSymbol = '$';
        $currencyCode = $defaultCurrency;
        // check if there is an auth user
        if(auth()->check()){
            // get user local currency form it country
            $user = auth()->user();
            $city = $user->city;
            $currency = $city ? $city->country->currency : null;
            $currencyCode = $currency ? $currency : $defaultCurrency;
        }

        // get the base local currency value 
        $currency = Currency::where('code', $currencyCode)->first();

        // return the same amount of mony if the currency used is "USD" or the selected currency is not supported
        if($currencyCode == $defaultCurrency || empty($currency) || $currency->code == $defaultCurrency){
            $response = [
                'local' => [
                    'value'     => round($amount, 2),
                    'code'      => $defaultCurrency,
                    'symbol'    => $defaultSymbol 
                ],
                'original'  => [
                    'value'     => round($amount, 2),
                    'code'      => $defaultCurrency,
                    'symbol'    => $defaultSymbol 
                ]
            ];
            return $response;
        }else{
            $convertingValue = $currency->value_to_dollar;
            $localValue = $amount * $convertingValue;

            $response = [
                'local' => [
                    'value'     => round($localValue, 2),
                    'code'      => $currencyCode,
                    'symbol'    => $currency->symbol 
                ],
                'original'  => [
                    'value'     => round($amount, 2),
                    'code'      => $defaultCurrency,
                    'symbol'    => $defaultSymbol 
                ]
            ];
            return $response;
        }

    }

    public function updateCurrencies(){
        $client = new Client();

        // get countries data
        $request = $client->request('get', 'https://restcountries.eu/rest/v2/all');
        $countries = collect(json_decode($request->getBody()->getContents()));
        $currencies = $countries->pluck('currencies')->flatten();

        // get USD dollar to EUR value
        $apiKey = env('CONVERTER_API_KEY');
        $request = $client->request('get', 'http://free.currencyconverterapi.com/api/v5/convert', ['query' => ['q' => 'USD_EUR', 'compact' => 'y', 'apiKey' => $apiKey]]);
        $response = json_decode($request->getBody()->getContents());
        $convertingRation = $response->USD_EUR->val;

        // get currencies rates based to EUR
        $accessKey = env('FIXER_ACCESS_KEY');
        $request = $client->request('get', 'http://data.fixer.io/api/latest', ['query' => ['access_key' => $accessKey]]);
        $response = json_decode($request->getBody()->getContents());
        $rates = $response->rates;
        foreach($rates as $code => $rate){
            $value = $rate * $convertingRation;
            $currency = $currencies->where('code', $code)->first();
            $currencyData = [
                'code'              => $code,
                'name'              => $currency ? $currency->name : null,
                'symbol'            => $currency ? $currency->symbol : null,
                'value_to_dollar'   => $value
            ];

            $oldCurrency = Currency::where('code', $currencyData['code'])->first();
            if(empty($oldCurrency)){
                Currency::create($currencyData);
            }else{
                $oldCurrency->update($currencyData);
            }
        }
    }

    public function getAdmins(){
        $admins = User::role('super-admin')->get();
        return $admins;
    }

    public function getTestBase64Strings(){
        $base64Images = [
            'data:image/jpeg;base64,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',
        ];
        return $base64Images;
    }
}