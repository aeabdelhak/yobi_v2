<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class color extends Model
{

    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'status',
        'color_code',
        'id_shape',
        'id_image',

    ];
    public function shape()
    {
        $this->belongsTo(shape::class);
    }
    public function scopeOfShape($query, $id)
    {
        $query->where('id_shape', $id);
    }

}