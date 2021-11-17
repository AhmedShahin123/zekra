<?php

namespace App\Repositories\Country;

use App\Repositories\BaseRepositoryTrait;
use App\Models\Country;

class CountryRepository implements CountryRepositoryInterface
{
    //add basic methods to user repo shared across repos
    use BaseRepositoryTrait;

    private $model;


    public function __construct(Country $model)
    {
        $this->model = $model;
    }

    // any custom repo method related to user repo only
}
