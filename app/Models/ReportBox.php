<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportBox extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'subject',
        'reason',
        'report_by'
    ];
}
