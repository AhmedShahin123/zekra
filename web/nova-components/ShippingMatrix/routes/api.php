<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Courier;
use App\Models\City;
use App\Models\CourierZone;
use App\Models\CourierPrice;
use App\Models\User;
use App\Models\Country;

use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/user', function(){
    $user = User::with('courier')->find(auth()->id());
    $user->is_admin = $user->hasRole('super-admin');
    return response()->json($user);
});

Route::get('/couriers', function (Request $request) {
    $couriers = Courier::with('user')->get();
    return response()->json($couriers);
});

Route::get('/couriers/{id}', function (Request $request, $id) {
    $courier = Courier::with('user', 'zones.city.country', 'prices')->find($id);
    return response()->json($courier);
});

Route::post('/couriers/{id}/zones', function(Request $request, $id){
    $validator = \Validator::make($request->all(), [
        'zone'      => 'required|min:1|integer',
        'city'      => 'required_without:country',
        'country'   => 'required_without:city',

    ]);
    if($validator->fails()){
        return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
    }

    if(auth()->user()->hasRole('super-admin')){
        $courier_id = $id;
    }else{
        $courier = Courier::where('user_id', auth()->id())->first();
        $courier_id = $courier->id;
    }

    if(request('country') != null && request('city') == null){
        $cities = City::where('country_id', request('country'))->get();
        foreach($cities as $city){
            $data = [
                'courier_id'    => $courier_id,
                'city_id'       => $city->id,
                'zone'          => request('zone')
            ];
            if(CourierZone::where('courier_id', $courier_id)->where('city_id', $city->id)->count() == 0){
                $zone = CourierZone::create($data);
            }
            
        }
        return response()->json(['status'=>true,'msg' => 'new zone created successfully'], 200);
    }else{
        $data = [
            'courier_id'    => $courier_id,
            'city_id'       => request('city'),
            'zone'          => request('zone')
        ];

        if(CourierZone::where('courier_id', $courier_id)->where('city_id', request('city'))->count() > 0){
            return response()->json(['status' => false, 'msg' => 'This Zone has already added before'], 400);
        }
    
        $zone = CourierZone::create($data);
        if($zone){
            return response()->json(['status'=>true,'msg' => 'new zone created successfully'], 200);
        }else{
            return response()->json(['status'=>false,'msg' => 'something went wrong'], 400);
        }
    }
});

Route::post('/couriers/{id}/prices', function(Request $request, $id){
    $validator = \Validator::make($request->all(), [
        'zone'                      => 'required|min:1|integer',
        'primary_weight'            => 'required|gt:0|numeric',
        'primary_weight_price'      => 'required|gt:0|numeric',
        'additional_weight'         => 'required|gt:0|numeric',
        'additional_weight_price'   => 'required|gt:0|numeric',
    ]);
    if($validator->fails()){
        return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
    }

    $data = $request->only(['zone', 'primary_weight', 'primary_weight_price', 'additional_weight', 'additional_weight_price']);
    
    if(auth()->user()->hasRole('super-admin')){
        $courier_id = $id;
    }else{
        $courier = Courier::where('user_id', auth()->id())->first();
        $courier_id = $courier->id;
    }    
    
    $data['courier_id'] = $courier_id;

    if(CourierPrice::where('courier_id', $courier_id)->where('zone', request('zone'))->count() > 0){
        return response()->json(['status' => false, 'msg' => 'This Price has already added before'], 400);
    }

    $price = CourierPrice::create($data);
    if($price){
        return response()->json(['status'=>true,'msg' => 'new price created successfully'], 200);
    }else{
        return response()->json(['status'=>false,'msg' => 'something went wrong'], 400);
    }
});

Route::delete('/couriers/{courier_id}/zones/{zone_id}', function(Request $request, $courier_id, $zone_id){
    if(!auth()->user()->hasRole('super-admin')){
        $courier = Courier::where('user_id', auth()->id())->first();
        $zone = CourierZone::where('courier_id', $courier->id)->find($zone_id);
        if(empty($zone)){
            // user is trying to delete a zone don not belong to him
            return response()->json(['status'=>false,'msg' => 'Zone not found'], 404);
        }
    }else{
        $zone = CourierZone::find($zone_id);
    }
    
    $delete = $zone->delete();
    if($delete){
        return response()->json(['status'=>true,'msg' => 'zone deleted successfully'], 200);
    }else{
        return response()->json(['status'=>false,'msg' => 'something went wrong'], 400);
    }
});

Route::delete('/couriers/{courier_id}/prices/{price_id}', function(Request $request, $courier_id, $price_id){
    if(!auth()->user()->hasRole('super-admin')){
        $courier = Courier::where('user_id', auth()->id())->first();
        $price = CourierPrice::where('courier_id', $courier->id)->find($price_id);
        if(empty($price)){
            // user is trying to delete a price don not belong to him
            return response()->json(['status'=>false,'msg' => 'price not found'], 404);
        }
    }else{
        $price = CourierPrice::find($price_id);
    }
    
    $delete = $price->delete();
    if($delete){
        return response()->json(['status'=>true,'msg' => 'price deleted successfully'], 200);
    }else{
        return response()->json(['status'=>false,'msg' => 'something went wrong'], 400);
    }
});

Route::get('/countries', function (Request $request) {
    $countries = Country::all();
    return response()->json($countries);
});

Route::get('/countries/{id}/cities', function (Request $request, $id) {
    $cities = City::where('country_id', $id)->get();
    return response()->json($cities);
});