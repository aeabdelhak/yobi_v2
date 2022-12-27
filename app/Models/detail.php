<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detail extends Model
{
    use HasFactory;
    protected $fillable = [

        'amount',
        'price',
        'id_shape',
        'id_size',
        'id_color',
        'id_order',
        'id_offer',
    ];

    public function order()
    {
        return $this->belongsTo(order::class, 'id_order');
    }
    public function color()
    {
        return $this->hasOne(color::class, 'id', 'id_color');
    }
    public function size()
    {
        return $this->hasOne(size::class, 'id', 'id_size');
    }
    public function offer()
    {
        return $this->hasOne(size::class, 'id', 'id_offer');
    }
    public function shape()
    {
        return $this->hasOne(shape::class, 'id', 'id_shape');
    }

}