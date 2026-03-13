<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Itenerary;
use App\Models\Activity;
use App\Models\Dish;
use App\Models\VisitingPlace;
use App\Models\Destination;
use App\Http\Requests\CreateIteneraryRequest;
use App\Http\Requests\UpdateIteneraryRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;


class IteneraryController extends Controller
{

    /**
     * List itineraries
     */
    /**
     * @OA\Get(
     *     path="/api/itineraries",
     *     summary="List all itineraries",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by itinerary title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="Search by destination name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Itenerary::query()->with('destinations');

        if ($request->has('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        if ($request->has('destination')) {
            $query->whereHas('destinations', function ($q) use ($request) {
                $q->where('title', 'ilike', '%' . $request->destination . '%');
            });
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        return $query->paginate(10);
    }

    /**
     * Create itinerary with nested data
     */
    /**
     * @OA\Post(
     *     path="/api/itineraries",
     *     summary="Create a new itinerary",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "category", "image", "destinations"},
     *                 @OA\Property(property="title", type="string", example="Summer Trip"),
     *                 @OA\Property(property="category", type="integer", example=1),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(
     *                     property="destinations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="address", type="string"),
     *                         @OA\Property(property="places", type="array", @OA\Items(type="string")),
     *                         @OA\Property(property="activities", type="array", @OA\Items(type="string")),
     *                         @OA\Property(property="dishes", type="array", @OA\Items(type="string"))
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Itinerary created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(CreateIteneraryRequest $request)
    {

        $itinerary = DB::transaction(function () use ($request) {

            $itinerary = Itenerary::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'category_id' => $request->category,
                'status' => 'pending',
                'visited_at' => null,
            ]);

            $itinerary->image()->create([
                'path' => $request->file('image')->store('itineraries', 'public')
            ]);

            foreach ($request->destinations as $destData) {

                $destination = $itinerary->destinations()->create([
                    'title' => $destData['title'],
                    'address' => $destData['address'],
                ]);

                foreach ($destData['places'] ?? [] as $place) {
                    $destination->places()->create([
                        'name' => $place
                    ]);
                }

                foreach ($destData['activities'] ?? [] as $activity) {
                    $destination->activities()->create([
                        'name' => $activity
                    ]);
                }

                foreach ($destData['dishes'] ?? [] as $dish) {
                    $destination->dishes()->create([
                        'name' => $dish
                    ]);
                }
            }

            return $itinerary;
        });

        return response()->json(
            $itinerary->load('destinations.places', 'destinations.activities', 'destinations.dishes'),
            201
        );
    }

    /**
     * Show a single itinerary
     */
    /**
     * @OA\Get(
     *     path="/api/itineraries/{itinerary}",
     *     summary="Get a single itinerary details",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="itinerary",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Itinerary not found")
     * )
     */
    public function show(Itenerary $itinerary)
    {
        return $itinerary->load(
            'destinations.places',
            'destinations.activities',
            'destinations.dishes'
        );
    }


    /**
     * @OA\Post(
     *     path="/api/itineraries/{itinerary}",
     *     summary="Update an itinerary",
     *     description="Uses POST with _method=PUT for multipart/form-data compatibility",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="itinerary",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="category", type="integer"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "visiting", "visited", "canceled"}),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="removed_activities", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="removed_places", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="removed_dishes", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="removed_destinations", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(
     *                     property="destinations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="address", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Itinerary updated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateIteneraryRequest $request, Itenerary $itinerary)
    {
        $itinerary = DB::transaction(function () use ($request, $itinerary) {
            $itinerary->update([
                'title' => $request->title,
                'category_id' => $request->category,
                'status' => $request->status,
                'visited_at' => ($itinerary->status!="visited" && $request->status==="visited")? now(): $itinerary->visited_at,
            ]);

            if($request->hasFile('image') &&$request->image != $itinerary->image?->path){
                DB::transaction(function () use ($request, $itinerary) {
                    $itinerary->image?->delete();
                    Storage::disk('public')->delete($itinerary->image?->path);
                    $itinerary->image()->create([
                        'path' => $request->file('image')->store('itineraries', 'public')
                    ]);
                });
                
            }

            foreach ($request->removed_activities ?? [] as $id) {
                Activity::find($id)?->delete();
            }

            foreach ($request->removed_places ?? [] as $id) {
                VisitingPlace::find($id)?->delete();
            }

            foreach ($request->removed_dishes ?? [] as $id) {
                Dish::find($id)?->delete();
            }

            foreach ($request->removed_destinations ?? [] as $id) {
                Destination::find($id)?->delete();
            }

            foreach ($request->destinations as $destData) {

                $destination = $itinerary->destinations()->create([
                    'title' => $destData['title'],
                    'address' => $destData['address'],
                ]);

                foreach ($destData['places'] ?? [] as $place) {
                    $destination->places()->create([
                        'name' => $place
                    ]);
                }

                foreach ($destData['activities'] ?? [] as $activity) {
                    $destination->activities()->create([
                        'name' => $activity
                    ]);
                }

                foreach ($destData['dishes'] ?? [] as $dish) {
                    $destination->dishes()->create([
                        'name' => $dish
                    ]);
                }

            }

            return $itinerary;
        });

        return response()->json(
            $itinerary->load('destinations.places', 'destinations.activities', 'destinations.dishes'),
            200
        );
    }

    /**
     * Delete itinerary
     */
    /**
     * @OA\Delete(
     *     path="/api/itineraries/{itinerary}",
     *     summary="Delete an itinerary",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="itinerary",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Itinerary deleted"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Itenerary $itinerary)
    {
        $authorisation = Gate::inspect('delete-itenerary', $itinerary);
        if($authorisation->denied()){
            return response()->json([
                'message' => 'You are not authorized to delete this itinerary'
            ], 403);
        }
        $itinerary->delete();

        return response()->json([
            'message' => 'Itinerary deleted'
        ]);
    }

    /**
     * Get iteneraries of the authenticated user
     */
    /**
     * @OA\Get(
     *     path="/api/itineraries/my",
     *     summary="Get itineraries of the authenticated user",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function userItineraries(\Illuminate\Http\Request $request)
    {
        return $request->user()->iteneraries()->with('destinations')->paginate(10);
    }

    /**
     * Copy an itinerary
     */
    /**
     * @OA\Post(
     *     path="/api/itineraries/{itinerary}/copy",
     *     summary="Copy an itinerary",
     *     tags={"Itineraries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="itinerary",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=201, description="Itinerary copied successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function copy(Itenerary $itinerary)
    {
        $newItinerary = DB::transaction(function () use ($itinerary) {
            // Clone the itinerary
            $clone = $itinerary->replicate();
            $clone->user_id = auth()->id();
            $clone->status = 'pending';
            $clone->visited_at = null;
            $clone->save();

            // Clone image
            if ($itinerary->image) {
                $originalPath = $itinerary->image->path;
                $newPath = 'itineraries/' . uniqid() . '_' . basename($originalPath);
                Storage::disk('public')->copy($originalPath, $newPath);
                
                $clone->image()->create([
                    'path' => $newPath
                ]);
            }

            // Clone destinations and their children
            foreach ($itinerary->destinations()->with(['activities', 'places', 'dishes'])->get() as $destination) {
                $newDestination = $clone->destinations()->create([
                    'title' => $destination->title,
                    'address' => $destination->address,
                ]);

                foreach ($destination->activities as $activity) {
                    $newDestination->activities()->create(['name' => $activity->name]);
                }

                foreach ($destination->places as $place) {
                    $newDestination->places()->create(['name' => $place->name]);
                }

                foreach ($destination->dishes as $dish) {
                    $newDestination->dishes()->create(['name' => $dish->name]);
                }
            }

            return $clone;
        });

        return response()->json(
            $newItinerary->load('destinations.places', 'destinations.activities', 'destinations.dishes'),
            201
        );
    }
}