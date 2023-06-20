<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WareHouse extends Model
{
    use HasFactory;
    protected $fillable=[
        'branch',
        'organization_id',
        'assigned_user_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
    ];
}
