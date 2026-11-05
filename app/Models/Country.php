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

    public const DEFAULT_NAME = 'Ethiopia';

    /**
     * The country location forms preselect (Ethiopia), or null when it is not set up.
     */
    public static function defaultId(): ?int
    {
        return static::query()->where('name', self::DEFAULT_NAME)->value('id');
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function zone()
    {
        return $this->hasMany(Zone::class);
    }

    public function woreda()
    {
        return $this->hasMany(Woreda::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
    public function wareHouses()
    {
        return $this->hasMany(WareHouse::class);
    } 
}

