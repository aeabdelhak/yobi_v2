<?php

namespace App\Models;

use App\Enums\sharedStatus;
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
        'id_landing_page',
        'id_image',

    ];

    public function scopeOflanding($query, $id)
    {
        $query->where('id_landing_page', $id)->where('status', '!=', sharedStatus::$deleted);
    }
}