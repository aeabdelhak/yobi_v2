<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\color;
use App\Models\hasOffer;
use App\Models\landingPage;
use App\Models\offer;
use App\Models\shape;
use App\Models\size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class colorsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$landing, ['only' => ['newColor', 'toggleStatus']]);

    }

    public function get(Request $req)
    {
        $landing = landingPage::findorfail($req->id);
        $shape = shape::landing($req->id)->whereid($req->shapeId)->firstorfail();
        $color = color::where('colors.id', $req->colorId)->leftJoin('files', 'files.id', 'id_image')->ofshape($shape->id)->firstorfail(DB::raw('colors.name,color_code,colors.id,url,status'));
        $offers = offer::ofShape($req->shapeId)->get();

        foreach ($offers as $key => $offer) {
            $hasOffer = hasOffer::where('id_color', $req->colorId)->where('id_offer', $offer->id)->first();
            if ($hasOffer) {
                $offer->id_color = $hasOffer->id_color;
                $offer->path = FilesController::path($hasOffer->id_image);
                $offer->status = $hasOffer->status;
                $offer->idOffer = $offer->id;
            }

        }

        $sizes = size::ofColor($color->id)->get();
        return response()->json([
            'landing' => $landing,
            'shape' => $shape,
            'color' => $color,
            'offers' => $offers,
            'sizes' => $sizes,
        ]);
    }

    public function newColor(Request $req)
    {

        $color = new color();
        $color->name = $req->name;
        $color->color_code = $req->color_code;
        $color->id_shape = $req->id_shape;
        $color->id_image = FilesController::store($req->image);
        if ($color->save()) {
            $color = color::leftJoin('files', 'files.id', 'id_image')->where('colors.id', $color->id)->first(DB::raw('colors.name,color_code,colors.id,url'));

            return response()->json([
                'status' => "Success",
                'data' => $color,
            ]);
        }
    }

    public function toggleStatus(Request $req)
    {
        $color = color::find($req->id);
        $color->status = $color->status == sharedStatus::$active ? sharedStatus::$inActive : sharedStatus::$active;
        $color->save();
        return response()->json([
            'status' => 'Success',
            'data' => $color,
        ]);
    }

}