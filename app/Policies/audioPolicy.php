<?php

namespace App\Policies;

use App\Enums\permissions;
use App\Models\User;
use App\Models\audio;
use Illuminate\Auth\Access\HandlesAuthorization;

class audioPolicy
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
     * @param  \App\Models\audio  $audio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, audio $audio)
    {
        return $user->isAdmin() || ($audio->landing->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions));

    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user,audio $audio)
    {
        return $user->isAdmin() || ($audio->landing->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions));

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\audio  $audio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, audio $audio)
    {
        return $user->isAdmin() || ($audio->landing->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions));

    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\audio  $audio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, audio $audio)
    {
        return $user->isAdmin() || ($audio->landing->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions));

    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\audio  $audio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, audio $audio)
    {
        return $user->isAdmin() || ($audio->landing->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions));

    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\audio  $audio
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, audio $audio)
    {
        return $user->isAdmin() || ($audio->landing->id_store == $user->store()->id && in_array( permissions::$landing,$user->Permissions));

    }
}
