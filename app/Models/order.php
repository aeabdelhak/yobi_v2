<?php

namespace App\Models;

use App\Enums\orderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'phone',
        'city',
        'address',
        'status',
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

}