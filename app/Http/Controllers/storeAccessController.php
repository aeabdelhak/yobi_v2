<?php

namespace App\Http\Controllers;

use App\Enums\constants;
use App\Enums\permissions;
use App\Models\storeAccess;
use Illuminate\Http\Request;

class storeAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$staff);
        $this->middleware('permission:' . permissions::$store);
    }

    public function grant(Request $req)
    {
        $store = $req->cookie(constants::$storeCookieName);
        $user = $req->id_user;
        $storeAccess = storeAccess::firstorcreate(['id_store' => $store, 'id_user' => $user]);
        return res('success', 'successfuly activated ', $storeAccess);
    }
    public function delete(Request $req)
    {
        $id = $req->id_user;
        $store = $req->cookie(constants::$storeCookieName);
        storeAccess::where('id_user', $id)->where('id_store', $store)->delete();
        return res('success', 'successfuly desactivatd');

    }
}