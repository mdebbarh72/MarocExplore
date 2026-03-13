<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="MarocExplore API",
 *     version="1.0.0",
 *     description="API documentation for the MarocExplore project, providing endpoints for itinerary management, destinations, and user authentication."
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
