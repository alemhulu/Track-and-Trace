<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    public function regions()
    {
        return $this->hasMany(region::class);
    }

    public function zone(): HasMany
    {
        return $this->hasMany(zone::class);
    }

    public function woreda(): HasMany
    {
        return $this->hasMany(woreda::class);
    }
}

