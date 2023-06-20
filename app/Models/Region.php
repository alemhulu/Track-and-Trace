<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'is_city',
        'name',
        'country_id',
    ];

      /**
     * Get all of the zones for the Region
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zones()
    {
        return $this->hasMany(zone::class);
    }

    public function woreda()
    {
        return $this->hasMany(woreda::class);
    }

    public function country()
    {
        return $this->belongsTo(country::class, 'country_id');
    }
}
