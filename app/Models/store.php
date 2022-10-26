<?php

namespace App\Models;

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
        'domain',
        'facebook',
        'tiktok',

    ];

    public function icon()
    {
        return $this->hasOne(file::class, 'id', 'id_logo');
    }

}