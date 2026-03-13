<?php

namespace App\Policies;

use App\Models\Destination;
use App\Models\User;
use App\Models\Itenerary;
use Illuminate\Auth\Access\Response;

class DestinationPolicy
{
    /**
     * Determine whether the user can create destinations for a given itinerary.
     */
    public function create(User $user, Itenerary $itinerary): Response
    {
        if (!$user) return Response::deny("please authenticate to add destinations");
        
        return ($user->id === $itinerary->user_id)
            ? Response::allow()
            : Response::deny("you are unauthorized to add destinations to this itinerary, you are not the owner");
    }

    /**
     * Determine whether the user can update the destination.
     */
    public function update(User $user, Destination $destination): Response
    {
        if (!$user) return Response::deny("please authenticate to update destination");
        
        return ($user->id === $destination->itenerary->user_id)
            ? Response::allow()
            : Response::deny("you are unauthorized to update this destination, you do not own the itinerary");
    }

    /**
     * Determine whether the user can delete the destination.
     */
    public function delete(User $user, Destination $destination): Response
    {
        if (!$user) return Response::deny("please authenticate to delete destination");
        
        return ($user->id === $destination->itenerary->user_id)
            ? Response::allow()
            : Response::deny("you are unauthorized to delete this destination, you do not own the itinerary");
    }
}
