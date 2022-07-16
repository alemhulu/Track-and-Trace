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
        'contact_person_mobile',
        'contact_person_email',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'organization_type_id',
     ];
}
