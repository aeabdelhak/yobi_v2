<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class offer extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'label',
        'original_price',
        'promotioned_price',
        'status',
        'id_shape',
        'id_image',

    ];

    public function scopeOfShape($query, $id)
    {
        $query->where('id_shape', $id);
    }
}