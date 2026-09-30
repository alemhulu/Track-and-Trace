<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'from_ware_house_id',
        'to_ware_house_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(WareHouse::class, 'from_ware_house_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(WareHouse::class, 'to_ware_house_id');
    }

    public function scopeAccessibleBy($query, ?User $actor)
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess()) {
            return $query;
        }

        return $query->where(function ($routeQuery) use ($actor) {
            $routeQuery
                ->whereHas('fromWarehouse', function ($warehouseQuery) use ($actor) {
                    $warehouseQuery->accessibleBy($actor);
                })
                ->orWhereHas('toWarehouse', function ($warehouseQuery) use ($actor) {
                    $warehouseQuery->accessibleBy($actor);
                });
        });
    }
}
