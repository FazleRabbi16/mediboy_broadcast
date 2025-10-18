<?php

namespace App\Models;

use App\Models\StockProduct;
use App\Models\OpenProductBatch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable=[
        'pharmacy_id',
        'product_id',
        'm_sales_id',
        'off_sales_id',
        'stock_product_id',
        'stock_in',
        'stock_out',
        'batch_no',
        'expire_date',
        'mfg_lic_no',
        'ma_no',
        'batch_no',
        'mfg_date',
    ];
    public function stock_product():BelongsTo
     {
        return $this->belongsTo(StockProduct::class);
     }
     public function product():BelongsTo
     {
        return $this->belongsTo(Product::class);
     }
     public function openBatch():HasOne
     {
        return $this->hasOne(OpenProductBatch::class, 'batch_no', 'batch_no');
     }
}
