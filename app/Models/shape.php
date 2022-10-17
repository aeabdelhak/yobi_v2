<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shape extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
        'original_price',
        'promotioned_price',
        'id_landing_page',

    ];
    public function colors()
    {
        return $this->hasMany(color::class);
    }
    public function landingPage()
    {
        return $this->belongsTo(landingPage::class, 'id_landing_page', 'id');
    }
    public function scopeLanding($query, $id)
    {
        return $query->where('id_landing_page', $id);
    }

}