<?php

namespace App\Models;

use App\Enums\orderStatus;
use App\Enums\sharedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class landingPage extends Model
{
    use HasFactory;

    protected $fillable = [

        'id',
        'name',
        'domain',
        'description',
        'product_name',
        'product_description',
        'status',
        'id_store',
        'id_pallete',
        'id_poster',

    ];
    public function shapes()
    {
        return $this->hasMany(shape::class, 'id_landing_page', 'id');
    }
    public function activatedShapes()
    {
        return $this->hasMany(shape::class, 'id_landing_page', 'id')->where('shapes.status', sharedStatus::$active);
    }
    public function audios()
    {
        return $this->hasMany(audio::class, 'id_landing_page', 'id')->where('status', sharedStatus::$active);
    }
    public function images()
    {
        return $this->hasMany(image::class, 'id_landing_page', 'id');
    }
    public function offers()
    {
        return $this->hasMany(offer::class, 'id_landing_page', 'id');
    }
    public function results()
    {
        return $this->hasMany(userResult::class, 'id_landing_page', 'id');
    }

    public function cards()
    {
        return $this->hasMany(card::class, 'id_landing_page', 'id')->where('status', sharedStatus::$active);
    }
    public function orders()
    {
        return $this->hasMany(order::class, 'id_landing_page', 'id')->where('status', '!=', orderStatus::$deleted);
    }
    public function poster()
    {
        return $this->hasOne(file::class, 'id', 'id_poster');
    }
    public function store()
    {
        return $this->belongsTo(store::class, 'id_store');
    }
    public function pallete()
    {
        return $this->hasOne(colorPalette::class, 'id', 'id_pallete');
    }

    public function scopeOfStore($query, $id)
    {
        return $query->where('id_store', $id);
    }

    public function scopeId($query, $id)
    {
        $query->where('id', $id);
    }

    public static function hasManyShapes($id)
    {
        $count = shape::Landing($id)->where('status', sharedStatus::$active)->count();
        if ($count > 1) {
            return true;
        }
        return false;
    }
}