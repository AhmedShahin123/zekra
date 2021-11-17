<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any coupons.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('super-admin') || $user->can('view-coupon');
    }

    /**
     * Determine whether the user can view the language.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Coupon  $coupon
     * @return mixed
     */
    public function view(User $user, Coupon $coupon)
    {
        return $user->hasRole('super-admin') || $user->can('view-coupon');
    }

    /**
     * Determine whether the user can create coupons.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole('super-admin') || $user->can('create-coupon');
    }

    /**
     * Determine whether the user can update the coupon.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Coupon  $coupon
     * @return mixed
     */
    public function update(User $user, Coupon $coupon)
    {
        return $user->hasRole('super-admin') || $user->can('update-coupon');
    }

    /**
     * Determine whether the user can delete the coupon.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Coupon  $coupon
     * @return mixed
     */
    public function delete(User $user, Coupon $coupon)
    {
        return $user->hasRole('super-admin') || $user->can('delete-coupon');
    }

    /**
     * Determine whether the user can restore the coupon.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Coupon  $coupon
     * @return mixed
     */
    public function restore(User $user, Coupon $coupon)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the coupon.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Coupon  $coupon
     * @return mixed
     */
    public function forceDelete(User $user, Coupon $coupon)
    {
        return false;
    }
}
