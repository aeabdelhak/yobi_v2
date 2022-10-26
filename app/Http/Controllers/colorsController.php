<?php

namespace App\Http\Controllers;

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

/*     public function __construct()
{
$this->middleware('auth:api');
$this->middleware('permission:' . permissions::$landing, ['only' => ['newColor', 'toggleStatus']]);

} */

    public function get(Request $req)
    {

        $landing = landingPage::findorfail($req->id);

        $shape = shape::landing($req->id)->where('id', $req->shapeId)->firstorfail();
        $color = color::where('status', '!=', sharedStatus::$deleted)->where('colors.id', $req->colorId)->leftJoin('files', 'files.id', 'id_image')->ofshape($shape->id)->firstorfail(DB::raw('colors.name,color_code,colors.id,url,status'));
        $offers = offer::Oflanding($req->id)->get();

        foreach ($offers as $key => $offer) {
            $hasOffer = hasOffer::where('id_color', $req->colorId)->where('id_offer', $offer->id)->first();
            if ($hasOffer) {
                $offer->path = FilesController::path($hasOffer->id_image);
                $offer->id_color = $hasOffer->id;
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
            $color = color::leftJoin('files', 'files.id', 'id_image')->where('colors.id', $color->id)->first(DB::raw('colors.name,color_code,colors.id,url,status'));

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
    public function delete(Request $req)
    {
        color::whereid($req->id)->update(['status' => sharedStatus::$deleted]);
        return res('success', 'color successfully deleted ', true);
    }

    public function edit(Request $req)
    {
        $hasFile = $req->hasFile('image');
        $color = color::find($req->id);
        $color->name = $req->name;
        $color->color_code = $req->color_code;
        $oldimage = $color->id_image;
        $color->id_image = $hasFile ? $color->id_image = FilesController::store($req->image) : $oldimage;
        $color->save();
        if ($hasFile) {
            FilesController::delete($oldimage);
        }

        $color = color::where('status', '!=', sharedStatus::$deleted)->where('colors.id', $req->id)->leftJoin('files', 'files.id', 'id_image')->firstorfail(DB::raw('colors.name,color_code,colors.id,url,status'));

        return res('success', 'successfully updated', $color);

    }

}