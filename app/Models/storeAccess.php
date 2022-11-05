<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class storeAccess extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'id_user',
        'id_store',
    ];
}