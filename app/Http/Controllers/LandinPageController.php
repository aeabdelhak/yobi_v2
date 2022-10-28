<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\audio;
use App\Models\card;
use App\Models\colorPalette;
use App\Models\file;
use App\Models\landingPage;
use App\Models\offer;
use App\Models\shape;
use App\Models\size;
use App\Models\store;
use App\Models\userResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class LandinPageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['client']]);
        $this->middleware('permission:' . permissions::$landing, ['except' => ['client']]);

    }

    public function get(Request $req)
    {

        $landing = landingPage::findorfail($req->id);
        $shapes = shape::landing($landing->id)->get();
        $cards = card::ofLanding($landing->id)->get();
        $offers = offer::ofLanding($landing->id)->get();
        $audios = audio::leftjoin('files', 'files.id', 'id_file')->where('id_landing_page', $req->id)->get(DB::raw('owner,audio.id,url,path'));
        $results = userResult::join('files', 'files.id', 'user_results.id_image')->where('id_landing_page', $landing->id)->get(DB::raw('*,user_results.id as id'));

        return response()->json([
            'landing' => $landing,
            'shapes' => $shapes,
            'cards' => $cards,
            'audios' => $audios,
            'results' => $results,
            'offers' => $offers,
        ]);

    }

    public function only(Request $req)
    {
        try {
            $landing = landingPage::where('status', '!=', sharedStatus::$deleted)->where('id', $req->id)->firstorfail();

        } catch (Throwable $e) {
            return response(null, 404);
        }
        $palletes = colorPalette::all();
        return compact('landing', 'palletes');
    }

    public function all(Request $req)
    {
        return landingPage::where('status', '!=', sharedStatus::$deleted)->where('id_store', $req->id)->get();

    }

    public function newLanding(Request $req)
    {

        $store = store::findorfail($req->id_store);
        $fulldomain = strtolower(trim($req->domain . '.' . $store->domain));

        if (landingPage::whereDomain($fulldomain)->first()) {
            return res('fail', 'the domain is already connected to another landing page', null);
        }

        $landingPage = new landingPage();
        $landingPage->name = $req->name;
        $landingPage->description = $req->description;
        $landingPage->domain = $fulldomain;
        $landingPage->product_description = $req->product_description;
        $landingPage->product_name = $req->product_name;
        $landingPage->id_store = $req->id_store;
        $landingPage->id_poster = FilesController::store($req->poster);
        $landingPage->id_pallete = $req->id_pallete;
        if ($landingPage->save()) {
            try {
                if (env('APP_ENV') !== 'local') {
                    (new vercelController())->domainAdd($landingPage->domain);
                }

            } catch (Throwable $r) {}
            return res('Success', 'successfuly', $landingPage);
        }

    }
    public function client(Request $req)
    {
        $domain = $req->domain;
        try {
            $landing = landingPage::where('domain', strtolower($domain))->orwhere('id', $req->id)->with(['poster', 'pallete', 'cards'])->firstorfail();

        } catch (Throwable $e) {
            return response(null, 404);
        }
        $store = store::find($landing->id_store);
        $store->token = null;
        $store->secret_token = null;
        $store->logo = file::find($store->id_logo);
        $landing->audios = audio::leftjoin('files', 'files.id', 'id_file')->where('id_landing_page', $landing->id)->get(DB::raw('owner,audio.id,url,path'));
        foreach ($landing->shapes as $key => $shape) {
            if (count($shape->colors) == 0) {
                unset($landing->shapes[$key]);
            } else {
                foreach ($shape->colors as $key => $color) {
                    $color->image = file::find($color->id_image);
                    $color->offers = offer::join('has_offers', 'has_offers.id_offer', 'offers.id')->join('files', 'files.id', 'has_offers.id_image')->where('id_color', $color->id)->where('has_offers.status', sharedStatus::$active)->where('offers.status', sharedStatus::$active)->get(DB::raw(('id_color,path,offers.id,promotioned_price,original_price,label,has_offers.status,has_offers.id idOffer')));
                    $color->sizes = size::ofcolor($color->id)->where('status', sharedStatus::$active)->get();
                }
            }

        }

        if (count($landing->shapes) == 0) {
            response()->status(404);
        }

        $landing->results = userResult::join('files', 'files.id', 'user_results.id_image')->where('id_landing_page', $landing->id)->get(DB::raw('*,user_results.id as id'));

        $store->data = $landing;

        return compact('store');
    }
    public function delete(Request $req)
    {

        $landingPage = landingPage::findorfail($req->id);
        $domain = $landingPage->domain;
        $landingPage->status = sharedStatus::$deleted;
        $landingPage->domain = "{deleted-$landingPage->id}" . $landingPage->domain;
        if ($landingPage->save()) {
            try {
                if (env('APP_ENV') !== 'local') {
                    (new vercelController())->domainRemove($domain);
                }

            } catch (Throwable $r) {}
        }
        return res('success', 'successfuly', $landingPage);

    }
    public function edit(Request $req)
    {
        try {
            $landingPage = landingPage::findorfail($req->id);

        } catch (Throwable $e) {
            return response(null, 404);
        }

        $store = store::whereid($landingPage->id_store)->first();
        $fulldomain = strtolower(trim($req->domain . '.' . $store->domain));

        if (landingPage::where('id', '!=', $req->id)->whereDomain($fulldomain)->first()) {
            return res('fail', 'the domain is already connected to another landing page', null);
        }

        $hasFile = $req->hasFile('poster');
        $oldDomain = $landingPage->domain;
        $landingPage->name = $req->name;
        $landingPage->domain = $fulldomain;
        $landingPage->description = $req->description;
        $landingPage->id_pallete = $req->id_pallete;
        $landingPage->product_description = $req->product_description;
        $landingPage->product_name = $req->product_name;

        $oldposter = $landingPage->id_poster;

        $landingPage->id_poster = $hasFile ? FilesController::store($req->poster) : $oldposter;

        $landingPage->save();
        if ($hasFile) {
            FilesController::delete($oldposter);
        }
        if ($landingPage->domain != $oldDomain && env('APP_ENV') != 'local') {
            (new vercelController())->domainRemove($oldDomain);
            (new vercelController())->domainAdd($fulldomain);
        }

        return res('success', 'successfully updated', true);

    }

}