<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'route_id',
        'step_order',
    ];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function route()
    {
        return $this->belongsTo(DistributionRoute::class, 'route_id');
    }
}
