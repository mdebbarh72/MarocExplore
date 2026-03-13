<?php

use App\Http\Controllers\Api\IteneraryController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\AuthController;
use App\Models\Itenerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/itineraries/my', [IteneraryController::class, 'userItineraries']);
    Route::post('/itineraries/{itinerary}/copy', [IteneraryController::class, 'copy']);
    Route::post('/itineraries/{itinerary}/destinations', [DestinationController::class, 'store']);
    Route::apiResource('itineraries', IteneraryController::class);
    
    Route::apiResource('destinations', DestinationController::class)->only(['update', 'destroy']);
    
    Route::post('/logout', [AuthController::class, 'logOut']);

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
