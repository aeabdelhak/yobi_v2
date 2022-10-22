<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fbPixel extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'id_store',
    ];
}