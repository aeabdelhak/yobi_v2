<?php

namespace App\Http\Controllers;

use App\Enums\constants;
use App\Enums\newUserRes;
use App\Enums\sharedStatus;
use App\Enums\userRoles;
use App\Enums\userStatus;
use App\Models\permission;
use App\Models\store;
use App\Models\User;
use App\Types\newUserResponse;
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
        $response = new \App\Types\newUserResponse();

        $user = User::withTrashed()->where('email', $email)->first();

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
    public function newStaff($idStore, $email, $permissions)
    {
        DB::beginTransaction();
        try {
            $response = new newUserResponse();

            $store = store::find($idStore);
            $user = User::withTrashed()->where('email', $email)->first();

            if ($user) {
                if ($user->role == userRoles::$superAdmin) {
                    $response->status = newUserRes::$notAllowed;
                } else if 
                ($user->abilities->where('id_store', $store->id)->first()) {
                    if ($user->trashed()) {
                        $response->status = newUserRes::$restored;
                        $user->active = userStatus::$active;
                        $user->abilities->delete();
                        $user->restore();
                        $user->save();
                        $user->refresh();
                        $this->addPermissions($user, $store, $permissions);
                        $user->stores;
                        $user->avatar;
                        $response->user = $user;
                    } else {
                        $response->status = newUserRes::$exist;
                    }
                } else {
                    $user->role = userRoles::$user;
                    $this->addPermissions($user, $store, $permissions);
                    $user->active = userStatus::$active;
                    if ($user->trashed()) {
                        $user->restore();
                    }
                    $user->save();
                    $user->refresh();
                    $user->avatar;
                    $response->user = $user;
                    $response->status = newUserRes::$success;
                }
            } else {
                $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
                $password = substr($random, 0, 10);
                $user = new User();
                $user->email = $email;
                $user->password = Hash::make($password);
                $user->save();
                $user = User::withTrashed()->where('email', $email)->first();
                $this->addPermissions($user, $store, $permissions);
                $user->avatar;
                (new SendEmailController())->welcome($email, $password);

                $response->user = $user;
                $response->status = newUserRes::$success;

            }
            DB::commit();
        } catch (Throwable $r) {

            DB::rollBack();
            $response->status = newUserRes::$error;
            throw $r;
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
        })->where('role', '!=', userRoles::$superAdmin)->where(function ($query) use ($search) {
            return $query->where('users.name', 'like', '%' . $search . '%')->orwhere('email', 'like', '%' . $search . '%');

        })->get(DB::raw('users.id id, url ,users.name name, email'));

    }

    public function addPermissions(User $user, store $store, array $permissions)
    {
        $permissions = permission::whereIn('code', $permissions)->pluck('id');

        if ($permissions->count() != 0) {
            $insert[] = ['id_user' => $user->id,
                'id_store' => $store->id,
                'id_permission' => 7,
                'created_at' => now(),
                'updated_at' => now()];
            foreach ($permissions as $key => $permission) {
                $insert[] = [
                    'id_user' => $user->id,
                    'id_store' => $store->id,
                    'id_permission' => $permission,
                    'created_at' => now(),
                    'updated_at' => now()];
            }
            DB::table('has_permissions')->insert($insert);
        }
    }

}
