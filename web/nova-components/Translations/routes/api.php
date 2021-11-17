<?php

use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
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

Route::get('/languages', function (Request $request) {
    $languages = Language::all();
    return response()->json($languages);
});

Route::get('/countries', function (Request $request) {
    $languages = Country::all();
    return response()->json($languages);
});

Route::put('/countries/{id}/translations', function (Request $request, $id) {
    $locales = Language::pluck('locale')->toArray();
    $validator = Validator::make($request->all(), [
        'country_name'  => 'required',
        'locale'        => ['required', Rule::in($locales)]
    ]);
    if($validator->fails()){
        return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
    }

    $inputs = $request->only('country_name', 'locale');
    $inputs['country_id'] = $id;
    $translation = CountryTranslation::where('country_id', $id)->where('locale', $inputs['locale'])->first();
    if($translation){
        $translation->update(['country_name' => $inputs['country_name']]);
    }else{
        CountryTranslation::create($inputs);
    }

    return response()->json(['status' => true, 'msg' => 'Translation updated successfully']);
});


Route::get('/cities', function (Request $request) {
    $languages = City::all();
    return response()->json($languages);
});

Route::put('/cities/{id}/translations', function (Request $request, $id) {
    $locales = Language::pluck('locale')->toArray();
    $validator = Validator::make($request->all(), [
        'city_name'     => 'required',
        'locale'        => ['required', Rule::in($locales)]
    ]);
    if($validator->fails()){
        return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
    }

    $inputs = $request->only('city_name', 'locale');
    $inputs['city_id'] = $id;
    $translation = CityTranslation::where('city_id', $id)->where('locale', $inputs['locale'])->first();
    if($translation){
        $translation->update(['city_name' => $inputs['city_name']]);
    }else{
        CityTranslation::create($inputs);
    }

    return response()->json(['status' => true, 'msg' => 'Translation updated successfully']);
});
