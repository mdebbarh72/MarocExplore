<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

abstract class apiFormRequest extends FormRequest
{
    protected ?string $errorMessage = null;

    protected function failedAuthorization()
    {
        throw new AuthorizationException($this->errorMessage);
    }
}
