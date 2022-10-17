<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\hasPermission;
use App\Models\permission;
use Illuminate\Http\Request;

class permissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$staff);
    }

    public function allow(Request $req)
    {
        $id = $req->userId;
        $code = $req->code;
        $permission = permission::where('code', $code)->first();
        if (!$permission) {
            return null;
        }

        hasPermission::firstOrCreate(['id_permission' => $permission->id, 'id_user' => $id]);
        return response()->json(['status' => "success", 'data' => true]);

    }
    public function forbid(Request $req)
    {
        $id = $req->userId;
        $code = $req->code;
        $permission = permission::where('code', $code)->first();
        if (!$permission) {
            return null;
        }

        hasPermission::where('id_permission', $permission->id)->where('id_user', $id)->delete();
        return response()->json(['status' => "success", 'data' => true]);

    }
}