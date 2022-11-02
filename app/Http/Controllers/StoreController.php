<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\landingPage;
use App\Models\store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => 'client']);
        $this->middleware('permission:' . permissions::$store, ['except' => ['allStores', 'client']]);

    }
    public function get($id)
    {
        return store::where('stores.id', $id)
            ->where('status', '!=', sharedStatus::$deleted)
            ->join('files', 'files.id', '=', 'id_logo')
            ->get(DB::raw("stores.id  ,url ,stores.name,stores.created_at,description "))[0];
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
    public function allStores()
    {

        return store::leftjoin('files', 'files.id', '=', 'id_logo')
            ->where('status', '!=', sharedStatus::$deleted)
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