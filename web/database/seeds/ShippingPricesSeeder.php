<?php

use Illuminate\Database\Seeder;

use App\Models\City;
use App\Models\Courier;
use App\Models\CourierZone;
use App\Models\CourierPrice;

class ShippingPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(CourierZone::count() == 0 && CourierPrice::count() == 0){
            $couriers = Courier::all();
            $cities = City::all();
            foreach($couriers as $courier){

                // seed courier zones
                foreach($cities as $city){
                    $zoneData = [
                        'zone'          => mt_rand(1,3),
                        'city_id'       => $city->id,
                        'courier_id'    => $courier->id 
                    ];
                    CourierZone::firstOrCreate($zoneData);
                }

                // seed courier zone prices
                $zones = CourierZone::where('courier_id', $courier->id)->distinct('zone')->pluck('zone')->toArray();
                foreach($zones as $zone){
                    $priceData = [
                        'zone'                      => $zone,
                        'courier_id'                => $courier->id,
                        'primary_weight'            => mt_rand(300, 1500),
                        'primary_weight_price'      => mt_rand(10, 50),
                        'additional_weight'         => mt_rand(300, 1500),
                        'additional_weight_price'   => mt_rand(10, 50)
                    ];
                    CourierPrice::firstOrCreate($priceData);
                }

            }
        }
    }
}
