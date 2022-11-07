<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class event extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'price',
        'id_shape',
        'id_size',
        'id_color',
        'id_order',
        'id_offer',

    ];
}