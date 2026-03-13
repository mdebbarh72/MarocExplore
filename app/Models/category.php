<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class category extends Model
{
    public function iteneraries(): HasMany
    {
        return $this->hasMany(Itenerary::class);
    }
}
