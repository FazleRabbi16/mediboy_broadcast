<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use URL;
class Area extends Model
{
    use HasFactory;
    protected $appends = ['area_image_path'];
    protected $fillable = [
        'division','district','image'
     ];
    //
     public function getAreaImagePathAttribute()
     {
        return URL::to('/').'/storage/30min_area_images/'. $this->image;
     }


}
