<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $fillable = ['destination_id', 'name'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
