<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;
    protected $fillable=[
        "sales_id",
        "product_id",
        "max_retail_price",
        "min_offer_price",
        "purchase_price",
        "offer_price",
        "offer_reserve",
        "batch_no",
        "quantity"
    ];
}
