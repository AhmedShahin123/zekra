<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Models\SocialIdentity;

use App\Repositories\BaseRepositoryTrait;

class UserRepository implements UserRepositoryInterface
{
    //add basic methods to user repo shared across repos
    use BaseRepositoryTrait;

    private $model;
    private $socialModel;


    public function __construct(User $model, SocialIdentity $socialModel)
    {
        $this->model = $model;
        $this->socialModel = $socialModel;
    }

    // any custom repo method related to user repo only
}
