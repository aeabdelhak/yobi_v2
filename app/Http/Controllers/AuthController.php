<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as theAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
        $this->middleware('permission:' . permissions::$staff, ['only' => ['register', 'all', 'get']]);
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
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $password = substr($random, 0, 10);

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
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }
    public function all()
    {
        return User::all();

    }
    public function get($id)
    {
        $user = User::findorfail($id);
        $user->permissions = user::getAccess($id);
        return $user;

    }

    public function refresh()
    {
        return response()->json(Auth::refresh());
    }

}