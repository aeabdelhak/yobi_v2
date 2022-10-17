<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getPermissions()
    {
        return static::join('has_permissions', 'users.id', 'has_permissions.id_user')->join('permissions', 'has_permissions.id_permission', 'permissions.id')->pluck('code');
    }
    public function Permissions()
    {
        return $this->getPermissions();
    }

    public static function getAccess($id)
    {
        return static::join('has_permissions', 'users.id', 'has_permissions.id_user')->join('permissions', 'has_permissions.id_permission', 'permissions.id')->where('users.id', $id)->pluck('code');
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {

        return ['permissions' => $this->getPermissions()];
    }

}