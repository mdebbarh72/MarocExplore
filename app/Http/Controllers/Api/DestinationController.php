<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Itenerary;
use App\Http\Requests\CreateDestinationRequest;
use App\Http\Requests\UpdateDestinationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DestinationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/itineraries/{itinerary}/destinations",
     *     summary="Add a destination to an itinerary",
     *     tags={"Destinations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="itinerary",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "address"},
     *             @OA\Property(property="title", type="string", example="Sahara Desert"),
     *             @OA\Property(property="address", type="string", example="Merzouga"),
     *             @OA\Property(property="places", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="activities", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="dishes", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Destination added"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(CreateDestinationRequest $request, Itenerary $itinerary)
    {
        $destination = DB::transaction(function () use ($request, $itinerary) {
            $destination = $itinerary->destinations()->create($request->only(['title', 'address']));

            foreach ($request->places ?? [] as $place) {
                $destination->places()->create(['name' => $place]);
            }

            foreach ($request->activities ?? [] as $activity) {
                $destination->activities()->create(['name' => $activity]);
            }

            foreach ($request->dishes ?? [] as $dish) {
                $destination->dishes()->create(['name' => $dish]);
            }

            return $destination;
        });

        return response()->json($destination->load('places', 'activities', 'dishes'), 201);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/destinations/{destination}",
     *     summary="Update a destination",
     *     tags={"Destinations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="destination",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="places", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="activities", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="dishes", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Destination updated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateDestinationRequest $request, Destination $destination)
    {
        DB::transaction(function () use ($request, $destination) {
            $destination->update($request->only(['title', 'address']));

            if ($request->has('places')) {
                $destination->places()->delete();
                foreach ($request->places as $place) {
                    $destination->places()->create(['name' => $place]);
                }
            }

            if ($request->has('activities')) {
                $destination->activities()->delete();
                foreach ($request->activities as $activity) {
                    $destination->activities()->create(['name' => $activity]);
                }
            }

            if ($request->has('dishes')) {
                $destination->dishes()->delete();
                foreach ($request->dishes as $dish) {
                    $destination->dishes()->create(['name' => $dish]);
                }
            }
        });

        return response()->json($destination->load('places', 'activities', 'dishes'));
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/destinations/{destination}",
     *     summary="Delete a destination",
     *     tags={"Destinations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="destination",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Destination deleted"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Destination $destination)
    {
        $authorization = Gate::inspect('delete-destination', $destination);
        if ($authorization->denied()) {
            return response()->json([
                'message' => $authorization->message()
            ], 403);
        }

        $destination->delete();

        return response()->json(['message' => 'Destination deleted']);
    }
}
