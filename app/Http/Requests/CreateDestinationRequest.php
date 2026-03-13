<?php

namespace App\Http\Requests;

use App\Http\Requests\apiFormRequest;
use Illuminate\Support\Facades\Gate;

class CreateDestinationRequest extends apiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $itinerary = $this->route('itinerary');
        $authorization = Gate::inspect('create-destination', $itinerary);
        
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
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'places' => 'nullable|array',
            'places.*' => 'nullable|string',
            'activities' => 'nullable|array',
            'activities.*' => 'nullable|string',
            'dishes' => 'nullable|array',
            'dishes.*' => 'nullable|string',
        ];
    }
}
