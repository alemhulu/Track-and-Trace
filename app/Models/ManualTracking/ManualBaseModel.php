<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Model;

abstract class ManualBaseModel extends Model
{
    protected $guarded = ['id'];
}
