<?php

namespace App\Http\Controllers;

use App\Enums\orderStatus;
use App\Models\color;
use App\Models\detail;
use App\Models\hasOffer;
use App\Models\landingPage;
use App\Models\offer;
use App\Models\order;
use App\Models\shape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class orderController extends Controller
{
    public function newOrder(Request $req)
    {
        $order = new order();
        $order->name = $req->name;
        $order->city = $req->city;
        $order->address = $req->address;
        $order->phone = $req->phone;
        $order->id_landing_page = $req->id;
        if ($order->save()) {

            $offer = offer::find($req->id_offer);
            $offer_price = $offer ? ($offer->promotioned_price ?? $offer->original_price) : null;
            $shape = shape::find($req->id);
            $shape_price = $shape->promotioned_price ?? $shape->original_price;

            $detail = new detail();
            $detail->id_color = $req->id_color;
            $detail->id_order = $order->id;
            $detail->id_shape = $req->id_shape;
            $detail->id_offer = $req->id_offer;
            $detail->id_size = $req->id_size;
            $detail->price = $offer_price ?? $shape_price;
            $detail->save();
            return response()->json(['status' => 'success', 'data' => true]);
        }

    }

    public function getStatistics(Request $req)
    {
        $landingsId = landingPage::ofStore($req->storeId)->pluck('id');
        $statistcs = [
            [orderStatus::$new => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$new)->count()],
            [orderStatus::$verified => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$verified)->count()],
            [orderStatus::$shipping => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$shipping)->count()],
            [orderStatus::$delivered => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$delivered)->count()],
            [orderStatus::$canceled => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$canceled)->count()],
            [orderStatus::$noResponce => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$noResponce)->count()],
            [orderStatus::$callRequested => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$callRequested)->count()],
            [orderStatus::$callOv3 => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$callOv3)->count()],
            [orderStatus::$voiceMail => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$voiceMail)->count()],
            [orderStatus::$delayed => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$delayed)->count()],
            [orderStatus::$outOfArea => order::whereIn('id_landing_page', $landingsId)->where('status', orderStatus::$outOfArea)->count()],
        ];
        return $statistcs;

    }

    public function getOrders(Request $req)
    {

        $landingsId = landingPage::ofStore($req->storeId)->pluck('id');

        return order::whereIn('id_landing_page', $landingsId)->where(function ($query) use ($req) {
            if ($req->search) {
                return $query->where('name', 'like', '%' . $req->search . '%')->orwhere('phone', 'like', '%' . $req->search . '%');
            }

        })->where(function ($query) use ($req) {
            if ($req->status) {
                return $query->whereIn('status', explode(',', $req->status));
            }

        })->paginate(2);

    }

    public function getOrder($id)
    {

        $order = Order::findorfail($id);
        $details = detail::leftjoin('shapes', 'shapes.id', 'details.id_shape')->leftjoin('sizes', 'sizes.id', 'details.id_size')->leftjoin('colors', 'colors.id', 'details.id_color')->leftjoin('offers', 'offers.id', 'details.id_offer')->where('id_order', $order->id)->get(DB::raw('shapes.name shape , offers.label offer ,sizes.label size ,colors.name color ,offers.id offerId ,colors.id colorId,price '));
        foreach ($details as $key => $detail) {

            if ($detail->offerId && $detail->colorId) {
                $detail->image = hasOffer::join('files', 'files.id', 'has_offers.id_image')->where('id_offer', $detail->offerId)->value('path');
            } else if ($detail->colorId) {
                $detail->image = color::join('files', 'files.id', 'colors.id_image')->where('colors.id', $detail->colorId)->value('path');
            }

        }
        return compact('order', 'details');

    }

}