<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class audio extends Model
{

    use HasFactory;

    protected $fillable = [
        'id',
        'owner',
        'status',
        'id_file',
        'id_landing_page',
    ];
}