<?php

namespace App\Http\Controllers;

use App\Enums\constants;
use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Enums\userStatus;
use App\Models\landingPage;
use App\Models\store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Throwable;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => 'client']);
        $this->middleware('permission:' . permissions::$store, ['except' => ['allStores', 'client', 'select']]);

    }
    public function get($id)
    {
        return store::where('stores.id', $id)
            ->where('status', '!=', sharedStatus::$deleted)
            ->join('files', 'files.id', '=', 'id_logo')
            ->first(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description,domain "));
    }

    public function current(Request $req)
    {
        $id = $req->cookie(constants::$storeCookieName);
        if ($id) {
            return response($this->get($id));
        }

    }
    public function only(Request $req)
    {

        return store::where('stores.id', $req->id)
            ->where('status', '!=', sharedStatus::$deleted)
            ->with(['icon'])->first();
    }

    public function edit(Request $req)
    {
        try {
            $store = store::where('id', $req->id)->firstorfail();

        } catch (Throwable $e) {
            return response(null, 404);
        }

        $oldIcon = $store->id_logo;
        $oldDomain = $store->domain;

        $hasFile = $req->hasFile('logo');

        $store->name = $req->name;
        $store->description = $req->description;
        $store->token = $req->token;
        $store->secret_token = $req->secret_token;
        $store->fecebook_meta_tag = $req->fecebook_meta_tag;
        $store->domain = strtolower(trim($req->domain));
        $store->facebook = $req->facebook;
        $store->tiktok = $req->tiktok;
        $store->google = $req->google;
        $store->id_logo = $hasFile ? FilesController::store($req->logo) : $oldIcon;
        $store->save();

        if ($hasFile) {
            FilesController::delete($oldIcon);
        }

        $landings = landingPage::ofStore($store->id)->get();

        if ($oldDomain != $store->domain) {

            try {
                if (env('APP_ENV') != 'local') {
                    (new vercelController())->deleteStore('www.' . $oldDomain);
                    (new vercelController())->domainAdd('www.' . $store->domain);
                }

            } catch (\Throwable$th) {

            }

            foreach ($landings as $key => $landing) {
                $newDomain = explode('.', $landing->domain)[0] . '.' . $store->domain;
                landingPage::where('id', $landing->id)->update([
                    'domain' => $newDomain,
                ]);
                try {
                    if (env('APP_ENV') != 'local') {
                        (new vercelController())->domainRemove($landing->domain);
                        (new vercelController())->domainAdd($newDomain);
                    }

                } catch (\Throwable$th) {

                }
            }
        }

        return res('success', 'updated suuccessfuly', true);
    }

    public function select(Request $req)
    {

        $id = $req->id;

        $store = store::leftjoin('files', 'files.id', '=', 'id_logo')
            ->where('status', '!=', sharedStatus::$deleted)
            ->where('stores.id', $id)
            ->first(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description,domain "));

        if (!$store) {
            return response($store, 404)->withoutCookie(constants::$storeCookieName);
        }
        $user = JWTAuth::user();
        $access = $user->StoreAccess()->toArray();
        if ($user->status == userStatus::$superAdmin || in_array($store->id, $access)) {
            $cookie = cookie(constants::$storeCookieName, $store->id, 60 * 24, '/');
            return response(["store" => $store, "status" => "success"], 200)->cookie($cookie, null, null, null, false, false, false, null);
        }
        return response(["status" => 'fail', "message" => "you are not authorized"], 200)->withoutCookie(constants::$storeCookieName);
    }

    public function allStores()
    {
        $user = JWTAuth::user();
        if ($user->status == userStatus::$superAdmin) {
            return store::leftjoin('files', 'files.id', '=', 'id_logo')
                ->where('status', '!=', sharedStatus::$deleted)
                ->get(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description,domain "));
        }
        return store::leftjoin('files', 'files.id', '=', 'id_logo')
            ->join('store_accesses', 'store_accesses.id_store', 'stores.id')
            ->where('status', '!=', sharedStatus::$deleted)
            ->where('id_user', '!=', $user->id)
            ->get(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description,domain "));

    }

    public function newStore(Request $req)
    {

        $store = new store();
        $store->name = $req->name;
        $store->description = $req->description;
        $store->token = $req->token;
        $store->secret_token = $req->secret_token;
        $store->domain = $req->domain;
        $store->facebook = $req->facebook;
        $store->fecebook_meta_tag = $req->fecebook_meta_tag;
        $store->tiktok = $req->tiktok;
        $store->google = $req->google;
        $store->id_logo = FilesController::store($req->file('logo'));
        if ($store->save()) {
            $store = store::leftjoin('files', 'files.id', '=', 'id_logo')->where('stores.id', $store->id)->first(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description "));
            try {
                if (env('APP_ENV') != 'local') {
                    (new vercelController())->newStore($req->domain);
                    (new vercelController())->facebookVerificationRecord($req->domain, $req->fecebook_meta_tag);
                }
            } catch (Throwable $r) {

            };

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

    public function delete(Request $req)
    {
        $donains = landingPage::ofStore($req->id)->pluck('domain');
        if (env('APP_ENV') != 'local') {

            foreach ($donains as $key => $value) {
                (new vercelController())->domainRemove($value);
            }
        }
        landingPage::ofStore($req->id)->update(['domain' => 'deleted', 'status' => sharedStatus::$deleted]);
        $store = store::where('id', $req->id)->first();
        $store->status = sharedStatus::$deleted;
        $domain = $store->domain;
        $store->domain = 'deleted';
        $store->save();
        try {if (env('APP_ENV') != 'local') {
            (new vercelController())->deleteStore($domain);
        }
        } catch (Throwable $r) {
        }
        return res('success', 'store successfully deleted ', true);
    }

    public function client(Request $request)
    {
        $domain = $request->header('domain');
        if ($domain) {
            $domain = str_replace('www.', '', $domain);
            $store = store::whereDomain($domain)->first();
            return response()->json($store);

        }

    }

}