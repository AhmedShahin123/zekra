<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CountryPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('view-country');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function view(User $user, Country $country)
    {
        return (($user->hasRole('super-admin') && $user->can('view-country')))
    || ($user->can('view-country'));
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function update(User $user, Country $country)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function delete(User $user, Country $country)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function restore(User $user, Country $country)
    {
        return true;
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
        return true;
    }
}
