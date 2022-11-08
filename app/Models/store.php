<?php

namespace App\Models;

use App\Enums\orderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class store extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'description',
        'link',
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
        return $this->hasManyThrough(User::class, storeAccess::class, 'id_store', 'id', 'id', 'id_user')->where('users.status', '!=', orderStatus::$deleted);
    }
    public function landings()
    {
        return $this->hasMany(landingPage::class, 'id_store', 'id');
    }
    public function scopeId($q, $id)
    {
        return $q->where('id', $id);
    }

}