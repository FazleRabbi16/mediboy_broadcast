<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use URL;

class ProductImage extends Model
{
    use HasFactory;
    protected $appends = ['product_image_path'];
    protected $fillable = [
        'image',
        'product_id'
    ];

    //Images belongs to a perticular product
    public function product():BelongsTo
    {
    return $this->belongsTo(Product::class,'product_id');
    }
    //product image path
    public function getProductImagePathAttribute()
    {
       return URL::to('/').'/storage/product_images/'. $this->image;
    }
}


