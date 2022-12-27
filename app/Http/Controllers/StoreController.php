<?php

namespace App\Http\Controllers;

use App\Enums\constants;
use App\Enums\orderStatus;
use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Enums\userStatus;
use App\Exports\OrdersExport;
use App\Models\landingPage;
use App\Models\order;
use App\Models\store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Throwable;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['client', 'exportDataToExcel']]);
        $this->middleware('permission:' . permissions::$store, ['only' => ['edit', 'delete', 'newStore']]);
        $this->middleware('storeAccess', ['except' => ['newOrder', 'allStores', 'exportDataToExcel', 'select']]);

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
        if ($req->fromCookie) {
            $id = $req->cookie(constants::$storeCookieName);
        } else {
            $id = $req->id;
        }

        return store::where('stores.id', $id)
            ->where('status', '!=', sharedStatus::$deleted)
            ->with(['icon', 'users'])->first();
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
        $access = $user->hasAccess($id);
        if ($user->status == userStatus::$superAdmin || $access) {

            $cookie = cookie(constants::$storeCookieName, $store->id, 60 * 24, '/', null, true, true, false, 'None');

            return response(["store" => $store, "status" => "success"], 200)->withCookie($cookie);
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
            ->where('id_user', $user->id)
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

    public function dashboard(Request $req)
    {
        if (!JWTAuth::user()->isAdmin()) {
            return response(null, 401);
        }
        $store = store::id($req->cookie(constants::$storeCookieName))->with(['landings'])->first();
        $ids = [];

        foreach ($store->landings as $key => $value) {
            $ids[] = $value->id;
        }

        $allOver = order::whereIn('id_landing_page', $ids)
            ->whereNotIn('status', [
                orderStatus::$canceled,
                orderStatus::$new,
                orderStatus::$returned,
                orderStatus::$deleted,
            ])->sum('total_paid');
        $thisMonth = order::whereIn('id_landing_page', $ids)
            ->whereNotIn('status', [
                orderStatus::$canceled,
                orderStatus::$new,
                orderStatus::$returned,
                orderStatus::$deleted,

            ])->whereMonth('created_at', DB::raw('MONTH(CURDATE())'))->sum('total_paid');
        $lastSunday = Carbon::now()->isDayOfWeek(0) ?
        Carbon::now()->toDateString()
        : Carbon::now()
            ->previous(0)
            ->toDateString();
        $nextSaturday = Carbon::now()
            ->nextWeekendDay()
            ->toDateString();

        $thisWeek = order::whereIn('id_landing_page', $ids)
            ->whereNotIn('status', [
                orderStatus::$canceled,
                orderStatus::$new,
                orderStatus::$returned,
                orderStatus::$deleted,
            ])->whereBetween(DB::raw('DATE(created_at)'), [date($lastSunday), date($nextSaturday)])
            ->sum('total_paid');
        $thisDay = order::whereIn('id_landing_page', $ids)
            ->whereNotIn('status', [
                orderStatus::$canceled,
                orderStatus::$new,
                orderStatus::$returned,
                orderStatus::$deleted,

            ])->whereDate('created_at', DB::raw('DATE(CURDATE())'))->sum('total_paid');

        $lastOrders = order::whereIn('id_landing_page', $ids)->NotDeleted()->orderby('created_at', 'desc')->take(6)->get();

        return response(compact('allOver', 'thisMonth', 'thisDay', 'thisWeek', 'lastOrders'));
    }

    public function exportDataToExcel(Request $req)
    {

        $id = $req->id;
        return (new OrdersExport)->download('orders.xlsx', Excel::XLSX);

    }
}