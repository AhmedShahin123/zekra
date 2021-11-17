<?php

namespace App\Services\City;

use App\Repositories\City\CityRepositoryInterface;

class CityService implements CityServiceInterface
{
    private $cityRepository;

    public function __construct(CityRepositoryInterface $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    // city related business functionality

    public function allCities()
    {
        $city = $this->cityRepository->all();

        //dd($country);
        if (!$city) {
            return null;
        }
        return $city;
    }

    public function countryCities($country_id)
    {
        //dd($conditions);
        $city = $this->cityRepository->where(['country_id' => $country_id])->get();
        //dd($city);
        //dd($city);
        if (!$city) {
            return null;
        }
        return $city;
    }
}
