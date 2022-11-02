<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
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

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
        $this->middleware('permission:' . permissions::$staff, ['except' => ['avatarUpload', 'editName', 'editpassword', 'login']]);
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();

        if ($user->status == sharedStatus::$inActive) {
            return res('fail', 'this account is desactivated');
        }
        if ($user->status == sharedStatus::$deleted) {
            return res('fail', 'invalid credentials');
        }

        $user->avatar = FilesController::url($user->id_avatar);
        $user->permissions = Auth::user()->permissions();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ])->withCookie(cookie('authToken', $token, 99999, '/', true, true));

    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->status !== sharedStatus::$deleted) {
            return res('fail', 'there is already an account with this email');
        }

        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $password = substr($random, 0, 10);

        if ($user) {
            $user->name = $request->name;
            $user->status = sharedStatus::$active;
            $user->password = Hash::make($password);
            $avatar = $user->id_avatar;
            $user->id_avatar = null;
            $user->save();
            FilesController::delete($avatar);
            $permissions = permission::whereIn('code', $request->permissions)->pluck('id');
            hasPermission::where('id_user', $user->id)->delete();
            if ($permissions->count() != 0) {
                $insert = [];
                foreach ($permissions as $key => $permission) {
                    $insert[] = ['id_user' => $user->id, 'id_permission' => $permission, 'created_at' => now(), 'updated_at' => now()];
                }
                DB::table('has_permissions')->insert($insert);
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
            ]);

            $permissions = permission::whereIn('code', $request->permissions)->pluck('id');

            if ($permissions->count() != 0) {
                $insert = [];
                foreach ($permissions as $key => $permission) {
                    $insert[] = ['id_user' => $user->id, 'id_permission' => $permission, 'created_at' => now(), 'updated_at' => now()];
                }
                DB::table('has_permissions')->insert($insert);
            }
        }

        $emailsent = (new SendEmailController())->welcome($request->email, $password);

        if (!$emailsent) {
            $user->delete();
            return response()->json([
                'status' => 'fail',
                'message' => 'could not send email , try again',
                'data' => $user,
                'emailsent' => $emailsent,
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user,
        ]);
    }

    public function logout()
    {
        theAuth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function user()
    {
        $user = Auth::user();
        $user->avatar = FilesController::url($user->id_avatar);
        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }
    public function all()
    {
        return User::leftjoin('files', 'files.id', 'users.id_avatar')
            ->whereNotIn('status', [sharedStatus::$deleted, sharedStatus::$hidden])->
            get(DB::raw('users.id,users.name,email,url avatar,status'));

    }
    public function get($id)
    {
        try {
            $user = User::where('id', $id)->whereNotIn('status', [sharedStatus::$deleted, sharedStatus::$hidden])->firstorfail();

        } catch (Throwable $e) {
            return response(null, 404);
        }
        $user->permissions = user::getAccess($id);
        return $user;

    }

    public function refresh()
    {
        return response()->json(Auth::refresh());
    }
    public function avatarUpload(Request $req)
    {

        $user = Auth::user();
        $oldAvatar = $user->id_avatar;
        $user->id_avatar = FilesController::store($req->image);
        $user->save();
        if ($oldAvatar) {
            FilesController::delete($oldAvatar);
        }
        return res('success', 'avatar updated successfuly', FilesController::url(($user->id_avatar)));

    }
    public function editName(Request $req)
    {

        $user = Auth::user();
        $user->name = $req->name;
        $user->save();

        return res('success', 'name updated successfuly', $user->name);

    }
    public function editpassword(Request $req)
    {

        $user = Auth::user();
        $check = Hash::check($req->password, $user->password);
        if (!$check) {
            return res('fail', 'password incorrect !');
        }
        if (strlen($req->new_password) < 8) {
            return res('fail', 'new password must contain at least 8 characters');
        }
        if ($req->confirmation_password !== $req->new_password) {
            return res('fail', 'passwords do not match');
        }

        $user->password = hash::make($req->new_password);
        $user->save();

        return res('success', 'password updated successfuly', $user->name);

    }

    public function delete($id)
    {
        user::where('id', $id)->update(['status' => sharedStatus::$deleted]);
        return res('success', 'user deleted successfuly', true);

    }
    public function changeStatus(Request $req)
    {
        $status = $req->status;
        if (!in_array($status, [sharedStatus::$active, sharedStatus::$inActive])) {
            return res('fail', 'incorrect status code');
        }

        user::where('id', $req->id)->update(['status' => $status]);
        return res('success', 'status changed successfuly', true);

    }

}