<?php

namespace App\Repositories\City;

use App\Repositories\BaseRepositoryTrait;
use App\Models\City;

class CityRepository implements CityRepositoryInterface
{
    //add basic methods to user repo shared across repos
    use BaseRepositoryTrait;

    private $model;


    public function __construct(City $model)
    {
        $this->model = $model;
    }

    // any custom repo method related to user repo only
}
