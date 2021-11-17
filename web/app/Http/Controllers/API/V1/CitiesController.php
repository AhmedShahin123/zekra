<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\City\CityServiceInterface;

use App\Http\Resources\City as CityResource;

use App\Models\City;

class CitiesController extends Controller
{
    public $cityService;

    public function __construct(CityServiceInterface $cityService)
    {
        $this->cityService = $cityService;
    }

    public function getCities()
    {
        $cities = $this->cityService->allCities();
        $response = CityResource::collection($cities);
        return response()->json(['status' => true,'data' => $response]);
    }

    public function countryCities()
    {
        $cities = $this->cityService->countryCities(request('country_id'));
        $response = CityResource::collection($cities);
        return response()->json(['status' => true,'data' => $response]);
    }

    public function getCity($id)
    {
        $city = City::find($id);
        $response = new CityResource($city);
        return response()->json(['status' => true, 'data' => $response]);
    }
}
