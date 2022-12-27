<?php

namespace App\Models;

use App\Enums\sharedStatus;
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
        return  $this->belongsTo(shape::class, 'id_shape');
    }
    public function image()
    {
        return  $this->hasOne(file::class, 'id','id_image');
    }
    public function sizes()
    {
        return $this->hasMany(size::class, 'id_color');
    }
    public function offers()
    {
        return   $this->hasManyThrough(offer::class,hasOffer::class, 'id_offer','id','id','id_color');
    }


    public function scopeOfShape($query, $id)
    {
        return   $query->where('id_shape', $id);
    }
    public static function hasManySizes($id)
    {
        $count = size::ofColor($id)->where('status', sharedStatus::$active)->count();
        if ($count > 1) {
            return true;
        }
        return false;
    }
    public function scopeActive($query)
    {
        return   $query->where('status', sharedStatus::$active);
    }
    public function scopeId($query, $id)
    {
        return  $query->where('id', $id);
    }
}