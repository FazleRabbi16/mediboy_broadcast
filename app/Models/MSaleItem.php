<?php

namespace App\Models;

use App\Models\MSale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MSaleItem extends Model
{
    use HasFactory;
    protected $fillable=[
        "m_sales_id",
        "product_id",
        "max_retail_price",
        "batch_no",
        "purchase_price",
        "offer_price",
        "quantity",
        "total_amount",
        "total_profit"
    ];

    public function msale():BelongsTo
     {
        return $this->belongsTo(MSale::class,'m_sales_id');
     }
    public function product():BelongsTo
    {
       return $this->belongsTo(Product::class,'product_id');
    }
}

