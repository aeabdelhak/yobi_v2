<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class colorPalette extends Model
{
    protected $fillable = [
        'id',
        'name',
        'background',
        'text',
        'primary',
        'secondary',
    ];

    use HasFactory;
}