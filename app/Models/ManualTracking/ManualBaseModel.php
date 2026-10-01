<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class ManualBaseModel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
