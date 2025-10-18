<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $fillable =[
        "order_id",
        "user_id",
        "pharmacy_id",
        "parmacy_user_id",
        "salesPrice",
        "paymentMethod"
    ];
}
