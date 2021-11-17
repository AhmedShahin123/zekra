<?php

namespace App\Services\Country;

use App\Repositories\Country\CountryRepositoryInterface;

class CountryService implements CountryServiceInterface
{
    private $countryRepository;

    public function __construct(CountryRepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    // country related business functionality

    public function allCountries()
    {
        $country = $this->countryRepository->all();

        //dd($country);
        if (!$country) {
            return null;
        }
        return $country;
    }
}
