<?php

namespace App\Policies;

use App\Enums\permissions;
use App\Models\User;
use App\Models\landingPage;
use Illuminate\Auth\Access\HandlesAuthorization;

class landingPagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\landingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, landingPage $landingPage)
    {
        return $user->isAdmin() || ($landingPage->id_store == $user->store()->id && in_array( permissions::$orders,$user->Permissions));

    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->isAdmin() || in_array( permissions::$landing,$user->Permissions );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\landingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, landingPage $landingPage)
    {
        return $user->isAdmin() ||($landingPage->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions ));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\landingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, landingPage $landingPage)
    {
        return $user->isAdmin() ||($landingPage->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions ));

    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\landingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, landingPage $landingPage)
    {
        return $user->isAdmin();

    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\landingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, landingPage $landingPage)
    {
        return $user->isAdmin();
    }
}
