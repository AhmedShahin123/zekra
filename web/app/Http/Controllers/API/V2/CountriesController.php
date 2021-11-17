<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\Country\CountryServiceInterface;

class CountriesController extends Controller
{
    public $countryService;

    public function __construct(CountryServiceInterface $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
        * @SWG\GET(
        *     path="/api/v2/countries",
        *     description="get countries",
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


    public function getCountries()
    {
        $countries = $this->countryService->allCountries();

        return response()->json([
        'status' => true,
        'data' => $countries
        ], 200);
    }
}
