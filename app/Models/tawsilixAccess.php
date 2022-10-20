<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tawsilixAccess extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'token',
        'secret_token',

    ];
    protected $hidden = [
        'token',
        'secret_token',
    ];
}