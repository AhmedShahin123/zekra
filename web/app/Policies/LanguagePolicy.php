<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Language;
use Illuminate\Auth\Access\HandlesAuthorization;

class LanguagePolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any languages.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('super-admin') || $user->can('view-language');
    }

    /**
     * Determine whether the user can view the language.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Language  $language
     * @return mixed
     */
    public function view(User $user, Language $language)
    {
        return $user->hasRole('super-admin') || $user->can('view-language');
    }

    /**
     * Determine whether the user can create languages.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole('super-admin') || $user->can('create-language');
    }

    /**
     * Determine whether the user can update the language.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Language  $language
     * @return mixed
     */
    public function update(User $user, Language $language)
    {
        return $user->hasRole('super-admin') || $user->can('update-language');
    }

    /**
     * Determine whether the user can delete the language.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Language  $language
     * @return mixed
     */
    public function delete(User $user, Language $language)
    {
        return $user->hasRole('super-admin') || $user->can('delete-language');
    }

    /**
     * Determine whether the user can restore the language.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Language  $language
     * @return mixed
     */
    public function restore(User $user, Language $language)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the language.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Language  $language
     * @return mixed
     */
    public function forceDelete(User $user, Language $language)
    {
        return false;
    }
}
