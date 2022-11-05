<?php

namespace App\Http\Controllers;

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

    public function activate(Request $req)
    {
        $store = $req->id_store;
        $user = $req->id_user;
        $storeAccess = storeAccess::firstorcreate(['id_store' => $store, 'id_user' => $user]);
        return res('success', 'successfuly activated ', $storeAccess);
    }
    public function desactivate(Request $req)
    {
        $id = $req->id;
        storeAccess::where('id', $id)->delete();
        return res('success', 'successfuly desactivatd');

    }
}