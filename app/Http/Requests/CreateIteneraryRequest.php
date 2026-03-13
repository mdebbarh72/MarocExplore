<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\apiFormRequest;
use Illuminate\Support\Facades\Gate;
use App\Enums\IterenaryStatus;
use Illuminate\Validation\Rules\Enum;

class CreateIteneraryRequest extends apiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $authorization = Gate::inspect('create-itenerary', \App\Models\User::class);
        if($authorization->denied())
        {
            $this->errorMessage= $authorization->message();
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'title' => 'required|string|max:256',
            'category' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:png,jpg,jpeg|max:4096',
            'destinations' => 'required|array|min:2',
            'destinations.*.title' => "required|string|max:256|min:4",
            'destinations.*.address' => 'required|string|min:4|max:512',
            'destinations.*.places' => 'nullable|array',
            'destinations.*.places.*' => 'nullable|string',
            'destinations.*.dishes' => 'nullable|array',
            'destinations.*.dishes.*' => 'nullable|string',
            'destinations.*.activities' => 'nullable|array',
            'destinations.*.activities.*' => 'nullable|string',
            
        ];
    }

    public function messages(): array
{
    return [

        'title.required' => 'The itinerary title is required.',
        'title.string' => 'The itinerary title must be a valid string.',
        'title.max' => 'The itinerary title may not be greater than 256 characters.',

        'category.required' => 'Please select a category for the itinerary.',
        'category.exists' => 'The selected category does not exist.',

        'image.required' => 'An image for the itinerary is required.',
        'image.image' => 'The file must be a valid image.',
        'image.mimes' => 'The image must be a PNG, JPG, or JPEG file.',
        'image.max' => 'The image size must not exceed 4MB.',

        'destinations.required' => 'You must add at least two destinations.',
        'destinations.array' => 'Destinations must be provided as a list.',
        'destinations.min' => 'An itinerary must contain at least two destinations.',

        'destinations.*.title.required' => 'Each destination must have a title.',
        'destinations.*.title.string' => 'The destination title must be a valid string.',
        'destinations.*.title.min' => 'The destination title must be at least 4 characters.',
        'destinations.*.title.max' => 'The destination title may not exceed 256 characters.',

        'destinations.*.address.required' => 'Each destination must include an address.',
        'destinations.*.address.string' => 'The destination address must be a valid string.',
        'destinations.*.address.min' => 'The destination address must be at least 4 characters.',
        'destinations.*.address.max' => 'The destination address may not exceed 512 characters.',

        'destinations.*.places.array' => 'Places must be provided as a list.',
        'destinations.*.places.*.string' => 'Each place must be a valid string.',

        'destinations.*.dishes.array' => 'Dishes must be provided as a list.',
        'destinations.*.dishes.*.string' => 'Each dish must be a valid string.',

        'destinations.*.activities.array' => 'Activities must be provided as a list.',
        'destinations.*.activities.*.string' => 'Each activity must be a valid string.',
    ];
}


    
}
