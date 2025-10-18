<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentActivePrice extends Model
{
    use HasFactory;
    protected $fillable=[
     'pharmacy_id',
     'select_price'
    ];
}
