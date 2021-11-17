<?php

namespace App\Services\Order;

use App\Models\Courier;
use App\Models\CourierPrice;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;

class OrderPrice{

    protected $user;

    protected $address;

    protected $product;

    protected $partner;

    protected $courier;

    protected $albumsCount;

    public function __construct(User $user, UserAddress $address = null, Product $product = null){
        $this->user = $user;
        $this->address = $address ? $address : $user->defaultAddress;

        // set the product 
        if($product == null){
            $product = Product::find(1);
        }
        $this->product = $product;

        // set the default partner
        $partner = Partner::where('city_id', $this->address->city_id)->where('default', 1)->first();
        if (empty($partner)) { // if there is not default partner got to zekraHQ partner
            $partner = Partner::first();
        }
        $this->setPartner($partner);

        // set the default courier
        $courier = Courier::where('city_id', $this->partner->city_id)->where('default', 1)->first();
        if (empty($courier)) { // if there is not default partner got to zekraHQ courier
            $courier = Courier::first();
        }
        $this->setCourier($courier);

    }

    public function useOrder(Order $order){
        $this->user     = $order->user;
        $this->address  = $order->shippingAddress;
        $this->partner  = $order->partner;
        $this->courier  = $order->courier;
    }

    public function setPartner(Partner $partner){
        $this->partner = $partner;
    }

    public function getPartner(){
        return $this->partner;
    }

    public function setCourier(Courier $courier){
        $this->courier = $courier;
    }

    public function getCourier(){
        return $this->courier;
    }

    public function getShippingRates(){
        $courier = $this->courier;
        $shippingZone = $courier->zones->where('city_id', $this->address->city_id)->first();

        if(empty($shippingZone)){
            return new CourierPrice;
        }

        $shippingRates = $courier->prices->where('zone', $shippingZone->zone)->first();
        if(empty($shippingRates)){
            return new CourierPrice;
        }

        return $shippingRates;
    }

    public function getProduct(){
        return $this->product;
    }

    public function getProductPrice(){
        $productPrice = $this->product->prices()->where('city_id', $this->address->city_id)->first();
        $price = $productPrice ? $productPrice->price : 0;
        return $price;
    }

    public function getPriceVariables($albumsCount = 1){
        $this->albumsCount = $albumsCount;

        // calculate subtotal
        $subtotal = $this->calculateSubtotal();

        // calculate shipping
        $shipping = $this->calculateShipping();

        // calculate taxes
        $amount = $subtotal + $shipping;
        $taxes = $this->calculateTaxes($amount);

        $total = $subtotal + $shipping + $taxes;

        // calculate cash on deliver 
        $cod = $this->calculateCOD($total);

        $points = $this->albumsCount;

        $albumVariable = [
            'weight'    => $this->getProduct()->weight,
            'price'     => $this->getProductPrice()
        ];

        $shippingRates = $this->getShippingRates()->toArray();
        $shippingAddress = $this->address->toArray();
        $partner = $this->partner->toArray();
        $courier = $this->courier->toArray();

        $variables = [
            'albums'            => $albumVariable,
            'shippingRates'     => $shippingRates,
            'shippingAddress'   => $shippingAddress,
            'albumsCount'       => $this->albumsCount,
            'partner'           => $partner,
            'courier'           => $courier,
            'subtotal'          => $subtotal,
            'shipping'          => $shipping,
            'taxes'             => $taxes,
            'cod'               => $cod,
            'points'            => $points,
            'total'             => $total
        ];

        return $variables;
    }

    private function calculateSubtotal(){
        $price = $this->getProductPrice();
        $total = $price * $this->albumsCount;
        return $total;
    }

    private function calculateShipping(){
        $shippingRates = $this->getShippingRates();
        $product = $this->product;

        $totalWeight            = floatval($product->weight * $this->albumsCount);
        $primaryWeight          = floatval($shippingRates->primary_weight);
        $additionalWeight       = floatval($shippingRates->additional_weight);
        $primaryWeightPrice     = floatval($shippingRates->primary_weight_price);
        $additionalWeightPrice  = floatval($shippingRates->additional_weight_price);
        
        $price = $primaryWeightPrice;
        $totalWeight -= $primaryWeight;

        while ($totalWeight > 0 && $additionalWeight > 0) {
            $price += $additionalWeightPrice;
            $totalWeight -= $additionalWeight;
        }
        
        return $price;
    }

    private function calculateTaxes($amount){
        $tax = $this->address->city->tax;
        $taxPercentage = $tax / 100;
        $taxes = $amount * $taxPercentage;
        return $taxes;
    }

    // COD: Cash On Delivery
    private function calculateCOD($amount){
        $courier = $this->courier;
        $total = $amount;
        $cod = $courier->cash_delivery_primary_amount_fee;
        $total -= $courier->cash_delivery_primary_amount;
        
        while ($total > 0) {
            $cod += $courier->cash_delivery_additional_amount_fee;
            $total -= $courier->cash_delivery_additional_amount;
        }

        return $cod;
    }

}