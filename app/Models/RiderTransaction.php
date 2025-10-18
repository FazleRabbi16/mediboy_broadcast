<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderTransaction extends Model
{
    use HasFactory;
    protected $fillable=[
        "rider_id",
        "order_id",
        "tnxType",
        "tnxDateTime",
        "tnxMedia",
        "sender",
        "receiver",
        "tnxId",
        "amount"
    ];
}
