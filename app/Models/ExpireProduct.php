<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpireProduct extends Model
{
    use HasFactory;
    protected $fillable=[
        'pharmacy_id',
        'product_id',
        'batch_no',
        'quantity',
        'expire_date'
    ];
    public function product():BelongsTo
     {
        return $this->belongsTo(Product::class,'product_id');
     }
}
