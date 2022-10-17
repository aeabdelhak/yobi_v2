<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class landingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'link',
        'description',
        'status',
        'id_store',
        'id_pallete',
        'id_poster',

    ];
    public function shapes()
    {
        $this->hasMany(shape::class, 'id_landing_page', 'id');
    }

    public function scopeOfStore($query, $id)
    {
        return $query->where('id_store', $id);
    }
}