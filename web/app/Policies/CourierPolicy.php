<?php

namespace App\Policies;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CourierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('view-courier');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function view(User $user, Courier $courier)
    {
        return (($user->hasRole('super-admin') && $user->can('view-courier')))
      || ($user->can('view-courier'));
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole('super-admin') && $user->can('create-user');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function update(User $user, Courier $courier)
    {
        return $user->hasRole('super-admin') && $user->can('update-user');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function delete(User $user, Courier $courier)
    {
        return $user->hasRole('super-admin') && $user->can('delete-user');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function restore(User $user, Courier $courier)
    {
        return $user->hasRole('super-admin') && $user->can('delete-user');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        return $user->hasRole('super-admin') && $user->can('delete-user');
    }
}
