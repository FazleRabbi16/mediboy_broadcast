<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use URL;

class RiderFile extends Model
{
    use HasFactory;
    protected $appends = ['rider_document_path'];
    protected $fillable=[
        "rider_id",
        "file"
    ];
    // cover image path
    public function getRiderDocumentPathAttribute()
    {
       return URL::to('/').'/storage/rider_files/'. $this->file;
    }
}
