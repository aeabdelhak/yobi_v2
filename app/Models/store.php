<?php

namespace App\Models;

use App\Enums\orderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class store extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
        'token',
        'secret_token',
        'id_logo',
        'fecebook_meta_tag',
        'domain',
        'facebook',
        'tiktok',

    ];

    public function icon()
    {
        return $this->hasOne(file::class, 'id', 'id_logo');
    }
    public function users()
    {
        return $this->hasManyThrough(User::class, hasPermission::class, 'id_store', 'id', 'id', 'id_user')->distinct();
    }
    public function landings()
    {
        return $this->hasMany(landingPage::class, 'id_store', 'id');
    }

    public function orders()
    {
        return $this->hasMany(order::class, 'id_store', 'id')->where('status', '!=', orderStatus::$deleted);
    }

    public function scopeId($q, $id)
    {
        return $q->where('id', $id);
    }

}