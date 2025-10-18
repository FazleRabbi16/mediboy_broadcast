<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultOrFreeDelivery extends Model
{
    use HasFactory;
    protected $fillable=[
        "default_charge_in_area",
        "default_charge_all_bd"
    ];
}
