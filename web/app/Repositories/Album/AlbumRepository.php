<?php

namespace App\Repositories\Album;

use App\Models\Album;

use App\Repositories\BaseRepositoryTrait;

class AlbumRepository implements AlbumRepositoryInterface
{
    //add basic methods to user repo shared across repos
    use BaseRepositoryTrait;

    private $model;


    public function __construct(Album $model)
    {
        $this->model = $model;
    }

    // any custom repo method related to user repo only
}
