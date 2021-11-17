<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Refund;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any refunds.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view the refund.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function view(User $user, Refund $refund)
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can create refunds.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the refund.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function update(User $user, Refund $refund)
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can delete the refund.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function delete(User $user, Refund $refund)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the refund.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function restore(User $user, Refund $refund)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the refund.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Refund  $refund
     * @return mixed
     */
    public function forceDelete(User $user, Refund $refund)
    {
        return false;
    }
}
