<?php

namespace App\Http\Controllers;

use App\Enums\orderStatus as EnumsOrderStatus;
use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Models\color;
use App\Models\detail;
use App\Models\hasOffer;
use App\Models\landingPage;
use App\Models\offer;
use App\Models\order;
use App\Models\orderChange;
use App\Models\shape;
use App\Models\shippServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class orderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['newOrder']]);
        $this->middleware('permission:' . permissions::$orders, ['only' => ['getTotalPrice', 'getOrders', 'history', 'getOrderName', 'getStatistics', 'changeStatus']]);
        $this->middleware('permission:' . permissions::$delivery, ['only' => ['pushToDelivery']]);
    }

    public function edit(Request $req)
    {

        $order = order::find($req->id);
        if (!$order) {
            return res('fail', 'order not found');
        }

        $order->name = $req->name;
        $order->address = $req->address;
        $order->city = $req->city;
        $order->phone = $req->phone;
        $order->save();
        return res('success', 'order updated successfully', $order->fresh());

    }

    public function getTotalPrice($id)
    {
        $order = order::find($id);
        if (!$order) {
            return NAN;
        }
        $price = 0;

        foreach ($order->details as $key => $detail) {

            $offer = offer::find($detail->id_offer);
            $offer_price = $offer ? ($offer->promotioned_price ?? $offer->original_price) : null;
            $shape = shape::find($detail->id_shape);
            $shape_price = $shape->promotioned_price ?? $shape->original_price;

            $price += $offer_price ?? $shape_price;
        }
        return $price;
    }
    public function getOrderName($id)
    {
        $names = [];

        $details = detail::leftjoin('shapes', 'shapes.id', 'details.id_shape')->leftjoin('landing_pages', 'shapes.id_landing_page', 'landing_pages.id')->leftjoin('sizes', 'sizes.id', 'details.id_size')->leftjoin('colors', 'colors.id', 'details.id_color')->leftjoin('offers', 'offers.id', 'details.id_offer')->where('id_order', $id)->get(DB::raw('shapes.name shape , offers.label offer ,sizes.label size ,colors.name color  ,landing_pages.product_name'));
        foreach ($details as $key => $detail) {
            $name = $detail->product_name;
            if ($detail->shape) {
                $name .= ' . الشكل: ' . $detail->shape;
            }
            if ($detail->color) {
                $name .= ' . اللون: ' . $detail->color;
            }
            if ($detail->size) {
                $name .= ' . المقاس: ' . $detail->size;
            }
            if ($detail->offer) {
                $name .= ' . العرض: ' . $detail->offer;
            }

            $names[] = $name;
        }
        return implode(',', $names);

    }

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
            $shape_price = $shape ? $shape->promotioned_price ?? $shape->original_price : 0;

            $detail = new detail();
            $detail->id_color = $req->id_color;
            $detail->id_order = $order->id;
            $detail->id_shape = $req->id_shape;
            $detail->id_offer = $req->id_offer;
            $detail->id_size = $req->id_size;
            $detail->price = $offer_price ?? $shape_price;
            $detail->save();
            return response()->json(['status' => 'success', 'data' => $req->id ? $order->refresh() : true]);
        }

    }

    public function getStatistics(Request $req)
    {
        $landingsId = landingPage::ofStore($req->storeId)->pluck('id');
        $statistcs = [
            [EnumsOrderStatus::$new => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$new)->count()],
            [EnumsOrderStatus::$verified => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$verified)->count()],
            [EnumsOrderStatus::$shipping => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$shipping)->count()],
            [EnumsOrderStatus::$delivered => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$delivered)->count()],
            [EnumsOrderStatus::$canceled => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$canceled)->count()],
            [EnumsOrderStatus::$noResponce => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$noResponce)->count()],
            [EnumsOrderStatus::$callRequested => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$callRequested)->count()],
            [EnumsOrderStatus::$callOv3 => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$callOv3)->count()],
            [EnumsOrderStatus::$voiceMail => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$voiceMail)->count()],
            [EnumsOrderStatus::$delayed => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$delayed)->count()],
            [EnumsOrderStatus::$outOfArea => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$outOfArea)->count()],
            [EnumsOrderStatus::$collected => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$collected)->count()],
            [EnumsOrderStatus::$returned => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$returned)->count()],
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
        })->orderby('created_at', 'desc')->paginate(20);

    }

    public function getOrder($id)
    {

        $order = Order::findorfail($id);
        $details = detail::leftjoin('shapes', 'shapes.id', 'details.id_shape')->leftjoin('landing_pages', 'shapes.id_landing_page', 'landing_pages.id')->leftjoin('sizes', 'sizes.id', 'details.id_size')->leftjoin('colors', 'colors.id', 'details.id_color')->leftjoin('offers', 'offers.id', 'details.id_offer')->where('id_order', $order->id)->get(DB::raw('shapes.name shape , offers.label offer ,sizes.label size ,colors.name color ,offers.id offerId ,colors.id colorId,price ,landing_pages.name'));
        foreach ($details as $key => $detail) {

            if ($detail->offerId && $detail->colorId) {
                $detail->image = hasOffer::join('files', 'files.id', 'has_offers.id_image')->where('id_offer', $detail->offerId)->value('path');
            } else if ($detail->colorId) {
                $detail->image = color::join('files', 'files.id', 'colors.id_image')->where('colors.id', $detail->colorId)->value('path');
            }

        }
        return compact('order', 'details');

    }

    public function changeStatus(Request $req)
    {

        $id = $req->id;
        $date = $req->date;
        $order = order::find($id);
        if (!$order) {
            return res('fail', 'please inter a valid id');
        }

        $status = $req->status;
        if (in_array($status, [EnumsOrderStatus::$delayed, EnumsOrderStatus::$callRequested])) {
            if (!$date) {
                return res('fail', 'date field is required');
            }

        } else {
            $date = null;
        }
        $ship = shippServices::where('id_order', $id)->first();
        if ($ship && $ship->status == sharedStatus::$active) {
            $ship->status = sharedStatus::$inActive;
            $ship->save();
        }
        $order->status = $status;
        $order->status_date = $date;
        $order->save();
        $orderChange = new orderChange();
        $orderChange->id_order = $id;
        $orderChange->to_date = $date;
        $orderChange->status = $status;
        $orderChange->note = $req->note;
        $orderChange->save();

        return res('success', 'successfuly chnaged', $order->fresh());

    }
    public function pushToDelivery(Request $req)
    {
        $id = $req->id;
        $order = order::find($id);

        if ($order->status === EnumsOrderStatus::$pushedToDelivery) {
            return res('fail', 'this order is already pushed');
        }

        $order->status = EnumsOrderStatus::$pushedToDelivery;

        if ((new tawsilixController())->push(
            $id,
            $order->name,
            $order->city,
            $order->address,
            $order->phone,
            $id,
            $this->getOrderName($id),
            1,
            $req->note,
            0,
            0,
            $this->getTotalPrice($id))) {
            $order->save();
            $orderChange = new orderChange();
            $orderChange->id_order = $id;
            $orderChange->status = EnumsOrderStatus::$pushedToDelivery;
            $orderChange->note = $req->note;
            $orderChange->save();

            return res('success', 'pushed to tawsilix successfully', $order->fresh());
        }
        return res('fail', 'something went wrong');

    }
    public function history($id)
    {
        return orderChange::leftjoin('users', 'users.id', 'order_changes.id_user')->orderby('order_changes.created_at', 'desc')->oforder($id)->get(DB::raw('order_changes.status,note,name,email,order_changes.created_at date ,to_date'));

    }

}