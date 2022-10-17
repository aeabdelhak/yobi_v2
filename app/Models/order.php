<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'phone',
        'city',
        'address',
        'status',
        'paid',
        'id_landing_page',
    ];
}