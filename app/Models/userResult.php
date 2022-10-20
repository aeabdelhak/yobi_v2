<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_image',
        'id_landing_page',

    ];
}