<?php

namespace App\Policies;

use App\Models\Itenerary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IteneraryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user?
        Response::allow()
        :
        Response::deny("please authenticate to see iteneraries");
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): Response
    {
        return $user?
        Response::allow()
        :
        Response::deny("please authenticate to see iteneraries");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user?
        Response::allow()
        :
        Response::deny("please authenticate to create iteneraries");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Itenerary $itenerary): Response
    {
        if (!$user) return Response::deny("please authenticate to update itenerary");
        return ($user->id === $itenerary->user_id)?
        Response::allow()
        :
        Response::deny("you are unothorized to update this itenerary, you are not the owner");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Itenerary $itenerary): Response
    {
        if (!$user) return Response::deny("please authenticate to update itenerary");
        return ($user->id === $itenerary->user_id)?
        Response::allow()
        :
        Response::deny("you are unothorized to cancel this itenerary, you are not the owner");
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Itenerary $itenerary): Response
    {
        if (!$user) return Response::deny("please authenticate to update itenerary");
        return ($user->id === $itenerary->user_id)?
        Response::allow()
        :
        Response::deny("you are unothorized to restore this itenerary, you are not the owner");
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Itenerary $itenerary): bool
    {
        return false;
    }

    public function copy(User $user): Response
    {
        return $user? Response::allow() : Response::deny("please authenticate to performe this action");
    }
}
