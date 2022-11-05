<?php

namespace App\Models;

use App\Enums\userStatus;
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
        'id',
        'name',
        'email',
        'password',
        'status',
        'id_avatar',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'id_avatar',
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
        return static::join('has_permissions', 'users.id', 'has_permissions.id_user')->join('permissions', 'has_permissions.id_permission', 'permissions.id')->where('users.id', $this->id)->pluck('code');
    }
    public function StoreAccess()
    {
        return static::join('store_accesses', 'users.id', 'store_accesses.id_user')->join('stores', 'store_accesses.id_store', 'store_accesses.id')->where('users.id', $this->id)->pluck('id_store');
    }
    public function canAccessStore($iduser, $idStore)
    {
        $user = static::where('id', $iduser)->first();

        if ($user->status == userStatus::$superAdmin) {
            return true;
        }

        if ($idStore) {
            $access = storeAccess::where('id_store', $idStore)->where('id_user', $iduser)->first();
        } else {
            $access = static::join('store_accesses', 'users.id', 'store_accesses.id_user')->where('id_store', $iduser)->where('id_user', $this->id)->first();
        }

        return $access ? true : false;

    }

    public function hasAccess($store)
    {
        if ($this->status == userStatus::$superAdmin) {
            return true;
        }

        return static::join('store_accesses', 'users.id', 'store_accesses.id_user')->where('id_store', $store)->where('id_user', $this->id)->first() ? true : false;
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