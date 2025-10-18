<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class OffSaleItem extends Model
{
    use HasFactory;
    protected $fillable=[
        "off_sales_id",
        "product_id",
        "max_retail_price",
        "min_offer_price",
        "purchase_price",
        "offer_price",
        "sale_price",
        "percentage_off",
        "batch_no",
        "quantity"
    ];
    
    public function product():BelongsTo
    {
       return $this->belongsTo(Product::class,'product_id');
    }
}
