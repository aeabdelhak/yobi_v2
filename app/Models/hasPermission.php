<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class hasPermission extends Model
{
    use HasFactory;
    protected $fillable = [

        'id',
        'id_permission',
        'id_user',
        'id_store',

    ];
}