<?php

namespace App\Http\Requests;

use App\Http\Requests\apiFormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateDestinationRequest extends apiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $destination = $this->route('destination');
        $authorization = Gate::inspect('update-destination', $destination);
        
        if ($authorization->denied()) {
            $this->errorMessage = $authorization->message();
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'places' => 'nullable|array',
            'places.*' => 'nullable|string',
            'activities' => 'nullable|array',
            'activities.*' => 'nullable|string',
            'dishes' => 'nullable|array',
            'dishes.*' => 'nullable|string',
        ];
    }
}
