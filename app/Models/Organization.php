<?php

namespace App\Models;

use App\Models\User as ModelsUser;
use App\View\Components\user;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'email',
        'website',
        'logo',
        'telephone',
        'bank',
        'coordinate',
        'coordinate',
        'gps',
        'year',
        'old_code',
        'new_code',
        'facilities',
        'assigned_user_id',
        'manager_name',
        'phone',
        'location',
        'status',
        'organization_type_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'sector_id',
        'ownership_id',
        'user_id',
     ];

     public function users()
     {
         return $this->hasMany(ModelsUser::class);
     }


     public function contact()
    {
        return $this->belongsTo(ModelsUser::class, 'assigned_user_id');
    }

     public function woreda()
     {
         return $this->belongsTo(Woreda::class, 'woreda_id');
     }   
 
     public function zone()
     {
         return $this->belongsTo(Zone::class, 'zone_id');
     }   
 
     public function region()
     {
         return $this->belongsTo(Region::class, 'region_id');
     }   
 
     public function country()
     {
         return $this->belongsTo(Country::class, 'country_id');
     }  

     public function ownership()
     {
         return $this->belongsTo(Ownership::class, 'region_id');
     }   

     public function organizationType()
     {
         return $this->belongsTo(OrganizationType::class, 'organization_type_id');
     }   
}
