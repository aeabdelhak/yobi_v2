<?php

namespace App\Http\Controllers;

use App\Enums\sharedStatus;
use App\Models\hasOffer;
use App\Models\offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class offerController extends Controller
{
    public function newOffer(Request $req)
    {
        $offer = new offer();
        $offer->label = $req->label;
        $offer->original_price = $req->original_price;
        $offer->promotioned_price = $req->promotioned_price;
        $offer->id_landing_page = $req->id;
        $offer->save();
        return response()->json(['status' => 'success', 'data' => $offer]);

    }
    public function assign(Request $req)
    {
        $color = $req->color;
        $offer = $req->offer;
        $already = hasOffer::where('id_offer', $offer)->where('id_color', $color)->first();
        if ($already) {
            return res('exist', 'allready assigned', false);

        }

        $image = FilesController::store($req->image);
        $hasOffer = new hasOffer();
        $hasOffer->id_offer = $offer;
        $hasOffer->id_image = $image;
        $hasOffer->id_color = $color;
        $hasOffer->save();

        $offer = offer::join('has_offers', 'has_offers.id_offer', 'offers.id')->join('files', 'files.id', 'has_offers.id_image')->where('id_color', $color)->where('has_offers.id', $hasOffer->id)->get(DB::raw(('id_color,path,offers.id,promotioned_price,original_price,label,has_offers.status,has_offers.id idOffer')))[0];

        return res('success', 'successfully created', $offer);

    }

    public function setActive(Request $req)
    {
        $hasOffer = hasOffer::find($req->id);
        $hasOffer->status = sharedStatus::$active;
        $hasOffer->save();
        return res('success', 'activated', true);
    }
    public function setInActive(Request $req)
    {
        $hasOffer = hasOffer::find($req->id);
        $hasOffer->status = sharedStatus::$inActive;
        $hasOffer->save();
        return res('success', 'desactivaed', true);
    }

    public function edit(Request $req)
    {
        $offer = offer::whereid($req->id)->update($req->all());
        return res('success', 'offer updated successfully', offer::find($req->id));
    }
    public function delete(Request $req)
    {
        $offer = offer::whereid($req->id)->update(['status' => sharedStatus::$deleted]);
        return res('success', 'offer successfully deleted ', $offer);
    }
}