<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'pharmacy_id',
        'TnxType',
        'TnxMedia',
        'sender',
        'receiver',
        'amount',
        'TnxDateTime',
     ];
}
