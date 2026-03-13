<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Itenerary extends Model
{
    protected $fillable= ['user_id', 'category_id', 'title', 'status', 'visited_at', 'created_at', 'updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function Image(): MorphOne
    {
        return $this->morphOne(Image::class,"imageable");
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
