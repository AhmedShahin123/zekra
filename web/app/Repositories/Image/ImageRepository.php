<?php

namespace App\Repositories\Image;

use App\Models\Image;

use App\Repositories\BaseRepositoryTrait;

class ImageRepository implements ImageRepositoryInterface
{
    //add basic methods to user repo shared across repos
    use BaseRepositoryTrait;

    private $model;


    public function __construct(Image $model)
    {
        $this->model = $model;
    }

    // any custom repo method related to user repo only
}
