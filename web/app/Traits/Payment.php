<?php

namespace App\Traits;

use Exception;
use Stripe\Charge;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\OAuth\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\Stripe;

trait Payment{
    public function handlePayment($cardToken, $amount){
        $response = ['failed' => false, 'msg' => null, 'data' => null, 'status_code' => 200];
        try{
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // transfer amount from dollar to cent
            $amount = $amount * 100;
            $payment = Charge::create([
                "amount"        => $amount,
                "currency"      => "usd",
                "source"        => $cardToken,
                "description"   => "new payment"
            ]);
            $response['data'] = $payment;
        } catch(Exception $error) {
            $response['failed'] = true;
            $response['msg']    = $error->getMessage();
            if(
                $error instanceof RateLimitException        ||
                $error instanceof InvalidRequestException   ||
                $error instanceof AuthenticationException   ||
                $error instanceof ApiConnectionException    ||
                $error instanceof ApiErrorException
            ){
                $response['data']           = $error->getError();
                $response['status_code']    = $error->getHttpStatus();
            }else{
                $response['status_code']    = 500;
            }
            
        }
        return $response;
    }

    public function getAllowedPaymentMethods(){
        return ['credit_card', 'credit_points', 'cod'];   
    }
}