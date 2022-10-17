<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detail extends Model
{
    protected $fillable = [
        'amount',
        'price',
        'id_shape',
        'id_size',
        'id_color',
        'id_order',
        'id_offer',

    ];
    use HasFactory;
}