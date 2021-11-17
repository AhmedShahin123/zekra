<?php

namespace App\Services\City;

interface CityServiceInterface
{
    public function allCities();
    public function countryCities($conditions);
}
