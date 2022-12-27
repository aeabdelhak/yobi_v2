<?php

namespace App\Models;

use App\Enums\orderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

class order extends Model
{
    use HasFactory , SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'name',
        'phone',
        'city',
        'address',
        'status',
        'status_date',
        'paid',
        'total_paid',
        'id_landing_page',
    ];

    public function details()
    {
        return $this->hasMany(detail::class, 'id_order');
    }
    public function scopeId($query, $id)
    {
        $query->where('id', $id);
    }
    public function scopeNotDeleted($query)
    {
        $query->where('status', '!=', orderStatus::$deleted);
    }
    public function store()
    {
        $landing = landingPage::whereId($this->id_landing_page)->first();
        $store = store::whereId($landing->id_store)->value('id');
        return $store;
    }
    public function landing()
    {
        return $this->belongsTo(landingPage::class, 'id_landing_page', 'id');
    }

}