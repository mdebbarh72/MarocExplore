<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Destination extends Model
{
    protected $fillable= ['itenerary_id', 'title', 'address'];

    public function itenerary(): BelongsTo
    {
        return $this->belongsTo(Itenerary::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(VisitingPlace::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

}
