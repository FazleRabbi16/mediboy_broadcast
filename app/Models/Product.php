<?php

namespace App\Models;

use URL;
use App\Models\Cart;
use App\Models\Company;
use App\Models\Category;
use App\Models\CurrentStock;
use App\Models\ProductImage;
use App\Models\StockProduct;
use Illuminate\Database\Eloquent\Model;
use \Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory,HasEagerLimit;
    protected $appends = ['product_cover_image_path'];
    protected $fillable = [
        'productName',
        'genericName',
        'retail_max_price',
        'cart_qty_inc',
        'cart_text',
        'unit_in_pack',
        'quantity',
        'type',
        'prescription',
        'feature',
        'status',
        'description',
        'coverImage',
        'category_id',
        'company_id',
        'add_by',
    ];
     // Reffer multiple image under single product
     public function images():HasMany
     {
        return $this->hasMany(ProductImage::class);
     }
     public function stockProducts():HasMany
     {
        return $this->hasMany(StockProduct::class);
     }
     //product belongs to a perticular category
     public function category():BelongsTo
     {
        return $this->belongsTo(Category::class);
     }
     //product belongs to a perticular category
     public function company():BelongsTo
     {
        return $this->belongsTo(Company::class);
     }
     //product in cart
     public function cartItems():HasOne
     {
        return $this->hasOne(Cart::class);
     }
     // get current stock
     public function currentStock():HasOne
     {
        return $this->hasOne(CurrentStock::class);
     }
     // to build relation with Order Items
     public function orderItems()
     {
         return $this->hasMany(OrderItem::class);
     }

     // cover image path
     public function getProductCoverImagePathAttribute()
     {
        return URL::to('/').'/storage/product_cover_images/'. $this->coverImage;
     }

}
