<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
       'is_active',
       'printer_id',
       'moe_id',
       'region_id',
       'zone_id',
       'woreda_id',
       'school_id',
       'step',
    ];

    public function printer()
    {
        return $this->belongsTo(Organization::class, 'printer_id');
    }
    public function moe()
    {
        return $this->belongsTo(Organization::class, 'moe_id');
    }
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
    public function woreda()
    {
        return $this->belongsTo(Woreda::class, 'woreda_id');
    }
    public function school()
    {
        return $this->belongsTo(Organization::class, 'school_id');
    }
    public function tracks()
    {
        return $this->hasMany(Track::class);
    } 
}
