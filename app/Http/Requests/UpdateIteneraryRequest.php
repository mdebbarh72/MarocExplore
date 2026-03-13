<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateIteneraryRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $authorization = Gate::inspect("update", $this->itinerary);
        if($authorization->denied())
        {
            $this->errorMessage = $authorization->message();
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
            "title"=>"required|string|min:3",
            "category"=>"required|string",
            "image"=>"nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            "status"=>"required|in:pending,visiting,visited,canceled",
            "destinations"=>"required|array",
            "destinations.*.title"=>"required|string",
            "destinations.*.address" => "required|string|min:4|max:512",
            "destinations.*.places"=>"nullable|array",
            "destinations.*.places.*"=>"nullable|string",
            "destinations.*.dishes"=>"nullable|array",
            "destinations.*.dishes.*"=>"nullable|string",
            "destinations.*.activities"=>"nullable|array",
            "destinations.*.activities.*"=>"nullable|string",
            "removed_destinations"=>"nullable|array",
            "removed_destinations.*"=>"nullable|integer",
            "removed_places"=>"nullable|array",
            "removed_places.*"=>"nullable|integer",
            "removed_activities"=>"nullable|array",
            "removed_activities.*"=>"nullable|integer",
            "removed_dishes"=>"nullable|array",
            "removed_dishes.*"=>"nullable|integer",
        ];
    }

    public function messages(): array
    {
        return [
            "title.required"=>"Title is required",
            "title.min"=>"Title must be at least 3 characters",
            "category.required"=>"Category is required",
            "duration.required"=>"Duration is required",
            "duration.integer"=>"Duration must be an integer",
            "image.required"=>"Image is required",
            "image.image"=>"Image must be an image",
            "image.mimes"=>"Image must be a jpeg, png, jpg, or gif",
            "image.max"=>"Image must be less than 2MB",
            "destinations.required"=>"Destinations are required",
            "destinations.array"=>"Destinations must be an array",
            "destinations.*.title.required"=>"Destination name is required",
            "destinations.*.title.string"=>"Destination name must be a string",
            "destinations.*.address.required"=>"Destination address is required",
            "destinations.*.address.string"=>"Destination address must be a string",
            "destinations.*.places.nullable"=>"Places must be an array",
            "destinations.*.places.*.nullable"=>"Place name must be a string",
            "destinations.*.dishes.nullable"=>"Dishes must be an array",
            "destinations.*.dishes.*.nullable"=>"Dish name must be a string",
            "destinations.*.activities.nullable"=>"Activities must be an array",
            "destinations.*.activities.*.nullable"=>"Activity name must be a string",
            "removed_destinations.nullable"=>"Removed destinations must be an array",
            "removed_destinations.*.nullable"=>"Removed destination must be an integer",
            "removed_places.nullable"=>"Removed places must be an array",
            "removed_places.*.nullable"=>"Removed place must be an integer",
            "removed_activities.nullable"=>"Removed activities must be an array",
            "removed_activities.*.nullable"=>"Removed activity must be an integer",
            "removed_dishes.nullable"=>"Removed dishes must be an array",
            "removed_dishes.*.nullable"=>"Removed dish must be an integer",
        ];
    }
}
