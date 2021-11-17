<?php

namespace App\Http\Controllers\API\V1;

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

    public function getCountries()
    {
        $countries = $this->countryService->allCountries();

        return response()->json([
        'status' => true,
        'data' => $countries
        ], 200);
    }
}
