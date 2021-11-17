<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;

use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view-transaction');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function view(User $user, Transaction $transaction)
    {
        return (($user->hasRole('super-admin') || $user->can('view-transaction')));
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return (($user->hasRole('super-admin') || $user->can('create-transaction')));
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function update(User $user, Transaction $transaction)
    {
        if($transaction->type != 'adjustment'){
            return false;
        }

        return (($user->hasRole('super-admin') || $user->can('update-transaction')));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function delete(User $user, Transaction $transaction)
    {
        if($transaction->type != 'adjustment'){
            return false;
        }

        return (($user->hasRole('super-admin') || $user->can('update-transaction')));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function restore(User $user, Transaction $transaction)
    {
        return false;
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
        return false;
    }
}
