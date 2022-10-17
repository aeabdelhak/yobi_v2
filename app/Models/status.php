<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class status extends Model
{
    protected $fillable = [
        'id',
        'status',
        'description',

    ];
    use HasFactory;
}