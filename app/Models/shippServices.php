<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shippServices extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'id_order',
        'id_shipping',
        'status',
        'by',
    ];
}