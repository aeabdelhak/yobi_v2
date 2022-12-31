<?php

namespace App\Models;

use App\Enums\permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class colorPalette extends Model
{
    protected $fillable = [
        'id',
        'name',
        'background',
        'text',
        'primary',
        'secondary',
    ];

    use HasFactory;

    public static function boot() {
        parent::boot();
        static::deleting(function($card) {
            if(!(JWTAuth::user()->isAdmin() || in_array(permissions::$palletes,JWTAuth::user()->getPermissions())) )
                return abort(404);
        });
        static::creating(function($card) {
            if(!(JWTAuth::user()->isAdmin() || in_array(permissions::$palletes,JWTAuth::user()->getPermissions())) )
            return abort(404);
        });
  

    }
}