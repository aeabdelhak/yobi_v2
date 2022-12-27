<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
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
        return (new AuthController())->register($args['email'], $args['name'], $args['permissions']);
    }
    public function deleteAdmin($rootValue, array $args)
    {
        $order = User::whereId($args['id'])->first();
        if ($order) {
            $order->delete();
            return 1;
        }
        return 0;
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
    public function uploadAvatar($rootValue, array $args)
        {
            
            $user = Auth::user();
            $oldAvatar = $user->id_avatar;
            $user->id_avatar = FilesController::store($args['image']);
            $user->save();
            if ($oldAvatar) {
                FilesController::delete($oldAvatar);
            }
            return FilesController::url(($user->id_avatar));
        }

}