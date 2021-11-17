<?php

namespace App\Repositories\Order;

use App\Models\Order;

use App\Repositories\BaseRepositoryTrait;

class OrderRepository implements OrderRepositoryInterface
{
    //add basic methods to user repo shared across repos
    use BaseRepositoryTrait;

    private $model;


    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    // any custom repo method related to user repo only
}
