<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTo extends Model
{
    use HasFactory;
    protected $fillable=[
        "order_id",
        "deliveryTo",
        "deliveryContact",
        "deliveryAddress",
        "addressType"
    ];
}
