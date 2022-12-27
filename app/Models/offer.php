<?php

namespace App\Models;

use App\Enums\sharedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class offer extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'id',
        'label',
        'original_price',
        'promotioned_price',
        'status',
        'id_landing_page',

    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($offer) { // before delete() method call this
             $offer->hasOffers()->delete();
             // do the rest of the cleanup...
        });
    }

    public function landing()
    {
        return $this->belongsTo(landingPage::class, 'id_landing_page');
    }
    public function hasOffers()
    {
        return $this->hasMany(hasOffer::class, 'id_offer');
    }

    public function scopeId($query, $id)
    {
        $query->where('id', $id);
    }
    public function scopeActive($query)
    {
        $query->where('status', sharedStatus::$active);
    }
    public function scopeOflanding($query, $id)
    {
        $query->where('id_landing_page', $id)->where('status', '!=', sharedStatus::$deleted);
    }
}