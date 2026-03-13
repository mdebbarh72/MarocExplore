<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class Handler extends ExceptionHandler
{
    
    protected $levels = [];
    protected $dontReport = [];
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register()
    {
        //
    }

    public function render($request, Throwable $exception)
    {
        
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 403);
        }

        return parent::render($request, $exception);
    }
}