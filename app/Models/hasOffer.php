<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class hasOffer extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'id',
        'status',
        'id_image',
        'id_color',
        'id_offer',
    ];

    public function scopeJoinOffer($query)
    {
        return $query->join('offers', 'offers.id', 'has_offers.id_offer');
    }
    public function scopeId($query, $id)
    {
        return $query->where('id', $id);
    }

}