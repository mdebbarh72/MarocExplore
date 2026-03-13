<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        if($user->id != $model->id) return Response::deny("this profile doesn't belong to you");
        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): Response
    {
        return $user? Response::deny('authenticated user cannot signup') : Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        return 
        ($user->id === $model->id) ?
        Response::allow() 
        :
        Response::deny("this account does not belong to you");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        return 
        ($user->id === $model->id) ?
        Response::allow()
        :
        Response::deny('this account doesn\'t belong to you');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        return 
        ($user->id === $model->id) ?
        Response::allow()
        :
        Response::deny('this account doesn\'t belong to you');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    public function login(?User $user):Response
    {
        return $user ? Response::deny('authenticated user cannot login') : Response::allow();
    }
}
