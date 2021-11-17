<?php

namespace App\Http\Controllers\API\V2;

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

    /**
        * @SWG\GET(
        *     path="/api/v2/cities",
        *     description="get cities",
        *     tags = {"countries"},
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function getCities()
    {
        $cities = $this->cityService->allCities();
        $response = CityResource::collection($cities);
        return response()->json(['status' => true,'data' => $response]);
    }

    /**
        * @SWG\POST(
        *     path="/api/v2/countryCities",
        *     description="get cities by country id",
        *     tags = {"countries"},
        *     @SWG\Parameter(
        *         name="country_id",
        *         in="query",
        *         type="string",
        *         description="country_id",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function countryCities()
    {
        $cities = $this->cityService->countryCities(request('country_id'));
        $response = CityResource::collection($cities);
        return response()->json(['status' => true,'data' => $response]);
    }

    /**
        * @SWG\GET(
        *     path="/api/v2/cities/1",
        *     description="get cities by id",
        *     tags = {"countries"},
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function getCity($id)
    {
        $city = City::find($id);
        $response = new CityResource($city);
        return response()->json(['status' => true, 'data' => $response]);
    }
}
