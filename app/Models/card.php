<?php

namespace App\Models;

use App\Enums\permissions;
use App\Enums\userRoles;
use App\Policies\cardPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class card extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'title',
        'status',
        'body',
        'id_landing_page',
    ];


    public static function boot() {
        parent::boot();
        static::deleting(function($card) {
            if(!(new cardPolicy)->delete(JWTAuth::user(),$card))
                return abort(404);
        });
        static::creating(function($card) {
            if(!(new cardPolicy)->create(JWTAuth::user(),$card))
                return abort(404);
        });
        static::updating(function($card) {
            if(!(new cardPolicy)->update(JWTAuth::user(),$card))
                return abort(404);
        });


    }


    public function landing()
    {
        $this->belongsTo(landingPage::class,'id_landing_page');
    }
    public function scopeOfLanding($query, $id)
    {
        $query->where('id_landing_page', $id);
    }
    public function controle(User $user): bool
    {
        return true;
        return in_array(permissions::$landing, $user->Permissions()) ?? $user->role==userRoles::$superAdmin ;
    }


}