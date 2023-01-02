<?php

namespace App\Models;

use App\Enums\sharedStatus;
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

    public function scopeActive($query)
    {
        $query->where('status', sharedStatus::$active);
    }

    public function colors()
    {
        return $this->hasMany(color::class, 'id_shape');
    }
    public function activatedColors()
    {
        return $this->hasMany(color::class, 'id_shape')->where('status', sharedStatus::$active);
    }
    public function landing()
    {
        return $this->belongsTo(landingPage::class, 'id_landing_page', 'id');
    }
    public function scopeLanding($query, $id)
    {
        return $query->where('id_landing_page', $id)->where('status', '!=', sharedStatus::$deleted);
    }
    public function scopeId($query, $id)
    {
        $query->where('id', $id);
    }
    public static function hasManyColors($id)
    {
        
        $count = shape::find($id)->colors->count();
        if ($count > 1) {
            return true;
        }
        return false;
    }

}