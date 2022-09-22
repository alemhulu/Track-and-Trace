<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'website',
        'email',
        'logo',
        'telephone',
        'mobile',
        'assigned_user_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'kebele_id',
        'organization_type_id',
     ];

     public function type()
     {
         return $this->belongsTo(OrganizationType::class, 'organization_type_id');
     }

     public function contact()
    {
        return $this->belongsTo(user::class, 'assigned_user_id');
    }

     public function woreda()
     {
         return $this->belongsTo(woreda::class, 'woreda_id');
     }   
 
     public function zone()
     {
         return $this->belongsTo(zone::class, 'zone_id');
     }   
 
     public function region()
     {
         return $this->belongsTo(region::class, 'region_id');
     }   
 
     public function country()
     {
         return $this->belongsTo(country::class, 'country_id');
     }  
}
