<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAddress extends Model
{
    use HasFactory;
    protected $fillable=[
        'fullName',
        'contactNumber',
        'division',
        'district',
        'upazilla',
        'pickupPoint',
        'extraInfo',
        'type',
        'user_id'
    ];
}
