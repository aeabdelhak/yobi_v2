<?php

namespace App\GraphQL\Mutations;

use App\Enums\userRoles;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Models\hasPermission;
use App\Models\permission;
use App\Models\storeAccess;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

final class Authenticate
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }

    public function newAdmin($rootValue, array $args)
    {
        return (new AuthController())->register($args['email'], $args['name'], $args['password']);
    }
    public function newStaff($rootValue, array $args)
    {
        return (new AuthController())->newStaff($args['email'], $args['name'], $args['permissions']);
    }
    public function deleteAdmin($rootValue, array $args)
    {
        $user = User::whereId($args['id'])->first();
        hasPermission::where('id_user',$args['id'])->delete();
        storeAccess::where('id_user',$args['id'])->delete();
        $user->role=userRoles::$user;
        if ($user->save()) {
            return true;
        }
        return false;
    }
    public function changeName($rootValue, array $args)
    {
        $user = Auth::user();
        $user->name = $args['name'];
        return $user->save() ? true : false;

    }
    public function changePassword($rootValue, array $args)
    {
        $user = Auth::user();
        $check = Hash::check($args['password'], $args['password']);
        if (!$check) {
            return 0;
        }
        if (strlen($args['new_password']) < 8) {
            return 2;
        }
        if ($args['confirmation_password'] !== $args['new_password']) {
            return 3;
        }

        $user->password = hash::make($args['new_password']);
        $user->save();
        return 1;

    }
    public function toggleStatus($rootValue, array $args)
    {
        $user = User::find($args['id']);
        $user->active=$user->active==0 ?1:0;
        $user->save();
        return $user;

    }
    public function togglePermission($rootValue, array $args)
    {
        $idPerm=permission::getId($args['code']);
        if($idPerm){
                    $hasPerm=hasPermission::where('id_user',$args['id'])->where('id_permission',$idPerm)->first();
                    if($hasPerm){
                        $hasPerm->delete();
                        return false;
                    }
                    $hasPrem= new hasPermission();
                    $hasPrem->id_user=$args['id'];
                    $hasPrem->id_permission=$idPerm;
                    $hasPrem->save();
                    return true;

        }
    abort(400);

    }
    public function uploadAvatar($rootValue, array $args)
        {
            
            $user = Auth::user();
            $oldAvatar = $user->id_avatar;
            $user->id_avatar = FilesController::store($args['image']);
            $user->save();
            if ($oldAvatar) {
                FilesController::delete($oldAvatar);
            }
            
            return $user->avatar;
        }

}