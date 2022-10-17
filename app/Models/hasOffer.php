<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class hasOffer extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'status',
        'id_image',
        'id_color',
        'id_offer',
    ];

}