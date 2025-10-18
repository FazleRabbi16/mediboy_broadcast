<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderShiftPlaceWithCommission extends Model
{
    use HasFactory;
    protected $fillable=[
        'division',
        'district',
        'upazilla',
        'commission',
        'status',
    ];
}
