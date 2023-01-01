<?php

namespace App\Http\Controllers;

use App\Enums\constants;
use App\Enums\newUserRes;
use App\Enums\sharedStatus;
use App\Enums\userRoles;
use App\Models\hasPermission;
use App\Models\permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as theAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;
use Throwable;

class AuthController extends Controller
{

 

    public function register($email, $name, $password)
    {

        $user = User::withTrashed()->where('email', $email)->first();

        $response = new \App\Types\newUserResponse();

        $sendMail = true;

        if ($user) {

            $response->status = newUserRes::$exist;

        }


        if ($user->trashed()) {
            $user->name = $name;
            $user->role = userRoles::$user;
            $user->status = sharedStatus::$active;
            $user->password = Hash::make($password);
            $user->save();
            $user->restore();

            $response->status = newUserRes::$restored;
            $response->user = $user->refresh();

        } else if ($user) {

            $response->status = newUserRes::$exist;
            $sendMail = false;

        } else {

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $response->status = newUserRes::$success;
            $response->user = $user->refresh();
        }
        if ($sendMail) {
            (new SendEmailController())->welcome($email, $password);
        }
        return $response;

    }
    public function newStaff($email, $name, $permissions)
    {

        $user = User::withTrashed()->where('email', $email)->first();

        $response = new \App\Types\newUserResponse();

        $sendMail = true;

        if ($user) {
            $response->status = newUserRes::$exist;
        }

        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $password = substr($random, 0, 10);

        if ($user->trashed()) {
            $user->name = $name;
            $user->status = sharedStatus::$active;
            $user->password = Hash::make($password);
            $user->save();
            $user->restore();

            hasPermission::where('id_user', $user->id)->delete();
            $this->addPermissions($user->id,$permissions);

            $response->status = newUserRes::$restored;
            $user=$user->refresh();
            $user->avatar;
            $response->user = $user;

        } else if ($user) {
            if($user->role==userRoles::$user){
                hasPermission::where('id_user', $user->id)->delete();
                $user->role=userRoles::$admin;
                $this->addPermissions($user->id,$permissions);
                $user->save();
                $user=$user->refresh();
                $user->avatar;
                $response->user = $user;
                $response->status = newUserRes::$success;   
            }
            else 
            {
                    $response->status = newUserRes::$exist;
                    $sendMail = false;
            }
        } else {

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->addPermissions($user->id,$permissions);
            $response->status = newUserRes::$success;
            $user=$user->refresh();
            $user->avatar;
            $response->user = $user;
        }
        if ($sendMail) {
            (new SendEmailController())->welcome($email, $password);
        }
        return $response;

    }

    public function logout()
    {
        theAuth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ])->withoutCookie(constants::$refreshToken);
    }

    public function refresh()
    {
        $token = Auth::refresh();
        return response()->json($token)->withCookie(constants::$refreshToken, $token, null, null, null, true, true, false, 'None');
    }




    public function ungrantUsersSearch(Request $req)
    {
        $id = $req->cookie(constants::$storeCookieName);
        $search = $req->s;
        return user::leftjoin('store_accesses', 'store_accesses.id_user', 'users.id')->leftjoin('files', 'files.id', 'id_avatar')->where(function ($query) use ($id) {
            return $query->where('id_store', '!=', $id)->orwhereNull('id_store');
        })->where('role','!=', userRoles::$superAdmin)->where(function ($query) use ($search) {
            return $query->where('users.name', 'like', '%' . $search . '%')->orwhere('email', 'like', '%' . $search . '%');

        })->get(DB::raw('users.id id, url ,users.name name, email'));

    }

    public function addPermissions($userid,$permissions)
    {
        $permissions = permission::whereIn('code', $permissions)->pluck('id');

        if ($permissions->count() != 0) {
            $insert = [];
            foreach ($permissions as $key => $permission) {
                $insert[] = ['id_user' => $userid, 'id_permission' => $permission, 'created_at' => now(), 'updated_at' => now()];
            }
            DB::table('has_permissions')->insert($insert);
        }
    }

}