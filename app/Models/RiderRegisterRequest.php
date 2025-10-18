<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderRegisterRequest extends Model
{
    use HasFactory;
    protected $fillable=[
        'division',
        'district',
        'vehicle',
        'name',
        'contact',
        'email',
        'eighteenPlus',
        'termsCondition'
    ];
}
