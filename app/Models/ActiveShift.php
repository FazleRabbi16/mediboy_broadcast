<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveShift extends Model
{
    use HasFactory;
    protected $fillable=[
        "rider_id",
        "shift_id",
        "rider_shift_id",
        "division",
        "district",
        "upazilla",
        "vehicleType",
        "is_online",
        "on_delivery",
        "givenStartTime",
        "givenEndTime",
        "bodyTemp",
        "activeDateTime"
    ];
}
