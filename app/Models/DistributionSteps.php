<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionSteps extends Model
{
    use HasFactory;
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'route_id',
        'distribution_id',
        'step',
    ];

    public function distribution()
    {
        return $this->belongsTo(distribution::class, 'distribution_id');
    }

    public function route()
    {
        return $this->belongsTo(route::class, 'route_id');
    }
}
