<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use URL;
class RequestProduct extends Model
{
    use HasFactory;
    protected $appends = ['request_product_cover_image_path'];
    protected $fillable=[
        'pharmacy_id',
        'productName',
        'companyName',
        'type',
        'image',
        'pharmacy_user_id'
    ];
    public function getRequestProductCoverImagePathAttribute()
     {
        return URL::to('/').'/storage/request_product_cover_images/'. $this->image;
     }
}
