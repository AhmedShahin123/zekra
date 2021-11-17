<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Courier;
use App\Models\City;
use App\Models\CourierZone;
use App\Models\CourierPrice;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductPrice;

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



Route::get('/products', function (Request $request) {
    $products = Product::get();
    return response()->json($products);
});

Route::get('/products/{id}', function (Request $request, $id) {
    $prices = ProductPrice::where('product_id', $id)->with('city.country')->get();
    return response()->json($prices);
});


Route::delete('/deleteproductpirce/{id}', function (Request $request, $id) {
    $prices = ProductPrice::where('id', $id)->delete();
    return response()->json(['status'=>true,'msg' => 'price deleted successfully'], 200);
});

Route::post('/createproductprice/{id}', function (Request $request, $id) {
    $validator = \Validator::make($request->all(), [
        'price'      => 'required|min:1|integer',
        'city'      => 'required_without:country',
        'country'   => 'required_without:city',

    ]);
    if ($validator->fails()) {
        return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
    }


    $data = [
        'product_id'    => $id,
        'city_id'       => request('city'),
        'price'          => request('price'),
        'country_id'          => request('country')
    ];

    if (ProductPrice::where('city_id', request('city'))->where('product_id', $id)->where('country_id', request('country'))->count() > 0) {
        return response()->json(['status' => false, 'msg' => 'Price has already added before in this area'], 400);
    }
    $price = ProductPrice::create($data);

    if ($price) {
        return response()->json(['status'=>true,'msg' => 'price created successfully'], 200);
    } else {
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
