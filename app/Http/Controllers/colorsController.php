<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\color;
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
        $offers = offer::leftjoin('has_offers', 'has_offers.id_offer', 'offers.id')->leftjoin('files', 'files.id', 'has_offers.id_image')->ofShape($req->shapeId)->get(DB::raw(('id_color,path,offers.id,promotioned_price,original_price,label,has_offers.status,has_offers.id idOffer')));
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