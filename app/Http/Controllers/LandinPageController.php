<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\audio;
use App\Models\card;
use App\Models\landingPage;
use App\Models\shape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandinPageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['client']]);
        $this->middleware('permission:' . permissions::$landing, ['only' => ['get', 'all', 'newLanding']]);

    }

    public function get(Request $req)
    {
        $landing = landingPage::findorfail($req->id);
        $shapes = shape::landing($landing->id)->get();
        $cards = card::ofLanding($landing->id)->get();
        $audios = audio::leftjoin('files', 'files.id', 'id_file')->where('id_landing_page', $req->id)->get(DB::raw('owner,audio.id,url,path'));

        return response()->json([
            'landing' => $landing,
            'shapes' => $shapes,
            'cards' => $cards,
            'audios' => $audios,
        ]);

    }
    public function all(Request $req)
    {
        return landingPage::where('id_store', $req->id)->get();

    }

    public function newLanding(Request $req)
    {
        $landingPage = new landingPage();
        $landingPage->name = $req->name;
        $landingPage->description = $req->description;
        $landingPage->link = $req->link;
        $landingPage->id_store = $req->id_store;
        $landingPage->id_poster = FilesController::store($req->poster);
        $landingPage->id_pallete = $req->id_pallete;
        if ($landingPage->save()) {
            return response()->json([
                'status' => "Success",
                'data' => $landingPage,
            ]);
        }

    }
    public function client()
    {
        return null;
    }

}