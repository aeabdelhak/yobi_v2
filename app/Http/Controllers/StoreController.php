<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\fbPixel;
use App\Models\store;
use App\Models\tawsilixAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$store, ['only' => ['edit', 'new']]);

    }
    public function get($id)
    {
        return store::where('stores.id', $id)
            ->join('files', 'files.id', '=', 'id_logo')
            ->get(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description "))[0];
    }
    public function edit(Request $req)
    {
        $store = store::where('id', $req->id)->firstorfail();
        return true;
    }
    public function all()
    {

        return store::leftjoin('files', 'files.id', '=', 'id_logo')->get(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description "));
    }
    public function newStore(Request $req)
    {

        $tawsilixAccess = tawsilixAccess::firstOrCreate(['token' => $req->token, 'secret_token' => $req->secret_token]);

        $store = new store();
        $store->name = $req->name;
        $store->description = $req->description;
        $store->id_token = $tawsilixAccess->id;
        $store->id_logo = FilesController::store($req->file('logo'));
        if ($store->save()) {

            $pixel = new fbPixel();
            $pixel->key = $req->key;
            $pixel->id_store = $store->id;
            $pixel->save();
            $store = store::leftjoin('files', 'files.id', '=', 'id_logo')->where('stores.id', $store->id)->first(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description "));

            return [
                'status' => "success",
                "store" => $this->get($store->id),
            ];
        }
        return [
            'status' => "fail",
            "store" => $store,
        ];

    }

}