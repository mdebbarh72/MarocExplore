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
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Itenerary::query()->with('destinations');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('destination')) {
            $query->whereHas('destinations', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->destination . '%');
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
    public function show(Itenerary $itinerary)
    {
        return $itinerary->load(
            'destinations.places',
            'destinations.activities',
            'destinations.dishes'
        );
    }


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
    public function userItineraries(\Illuminate\Http\Request $request)
    {
        return $request->user()->iteneraries()->with('destinations')->paginate(10);
    }

    /**
     * Copy an itinerary
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