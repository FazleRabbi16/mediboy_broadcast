<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCharge extends Model
{
    use HasFactory;
    protected $fillable=[
        "division",
        "district",
        "upazilla",
        "delivery_charge"
    ];
/*
MSale
protected $fillable =[
        "order_id",
        "user_id",
        "pharmacy_id",
        "parmacy_user_id",
        "salesPrice",
        "paymentMethod"
    ];
MSaleItem
protected $fillable=[
        "m_sales_id",
        "product_id",
        "max_retail_price",
        "min_offer_price",
        "purchase_price",
        "offer_price",
        "offer_reserve",
        "batch_no",
        "quantity"
    ];
Stock
namespace App\Models;

use App\Models\StockProduct;
use App\Models\OpenProductBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;
    protected $fillable=[
        'pharmacy_id',
        'product_id',
        'sales_id',
        'stock_product_id',
        'stockIn',
        'stockOut',
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
     public function openBatch():HasOne
     {
        return $this->hasOne(OpenProductBatch::class, 'batch_no', 'batch_no');
     }
}

*/
}
