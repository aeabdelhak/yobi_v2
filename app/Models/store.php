<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class store extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'link',
        'status',
        'id_logo',

    ];
    use HasFactory;
}