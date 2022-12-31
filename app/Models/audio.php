<?php

namespace App\Models;

use App\Policies\audioPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

class audio extends Model
{

    use HasFactory;

    protected $fillable = [
        'id',
        'owner',
        'status',
        'id_file',
        'id_landing_page',
    ];

    public static function boot() {
        parent::boot();
        static::deleting(function($audio) {
            if(!(new audioPolicy)->delete(Auth::user(),$audio))
                return abort(404);
        });
        static::creating(function($audio) {
            if(!(new audioPolicy)->create(Auth::user(),$audio))
                return abort(404);
        });
        static::updating(function($audio) {
            if(!(new audioPolicy)->update(Auth::user(),$audio))
                return abort(404);
        });


    }

    public function file()
    {
        return $this->hasOne(file::class,'id','id_file');
    }
    public function landing()
    {
        return $this->belongsTo(landingPage::class,'id_landing_page');
    }
}