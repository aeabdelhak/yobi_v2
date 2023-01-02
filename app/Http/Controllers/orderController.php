<?php

namespace App\Http\Controllers;

use App\Enums\constants;
use App\Enums\orderStatus as EnumsOrderStatus;
use App\Enums\permissions;
use App\Enums\sharedStatus;
use App\Http\operations\orderOperations;
use App\Models\color;
use App\Models\detail;
use App\Models\event;
use App\Models\hasOffer;
use App\Models\landingPage;
use App\Models\offer;
use App\Models\order;
use App\Models\orderChange;
use App\Models\shape;
use App\Models\shippServices;
use App\Models\size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class orderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['newOrder']]);
        $this->middleware('permission:' . permissions::$orders, ['only' => ['getTotalPrice', 'history', 'getOrders', 'getStatistics', 'getOrderName', 'changeStatus', 'pushToDelivery']]);
        $this->middleware('storeAccess', ['except' => ['newOrder', 'getOrder']]);
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
        $order->total_paid = $req->total_paid;
        $order->save();
        return res('success', 'order updated successfully', $order->fresh());

    }


    public function getOrderName($id)
    {
        $names = [];

        $details = detail::leftjoin('shapes', 'shapes.id', 'details.id_shape')->leftjoin('landing_pages', 'shapes.id_landing_page', 'landing_pages.id')->leftjoin('sizes', 'sizes.id', 'details.id_size')->leftjoin('colors', 'colors.id', 'details.id_color')->leftjoin('offers', 'offers.id', 'details.id_offer')->where('id_order', $id)->get(DB::raw('shapes.name shape , offers.label offer,colors.id color_id ,sizes.label size ,colors.name color ,shapes.id id_shape ,landing_pages.product_name ,landing_pages.id id_landing'));

        if (count($details) == 1) {
            $detail = $details[0];
            $name = $detail->product_name;
            if ($detail->shape && landingPage::hasManyShapes($detail->id_landing)) {
                $name .= ' . الشكل: ' . $detail->shape;
            }
            if ($detail->color && shape::hasManyColors($detail->id_shape)) {
                $name .= ' . اللون: ' . $detail->color;
            }
            if ($detail->size && color::find($detail->color_id)->sizes->count()>0) {
                $name .= ' . المقاس: ' . $detail->size;
            }
            if ($detail->offer) {
                $name .= ' . العرض: ' . $detail->offer;
            }
            return $name;
        }

        foreach ($details as $key => $detail) {
            $name = $detail->product_name;
            if ($detail->shape && landingPage::hasManyShapes($detail->id_landing)) {
                $name .= ' . الشكل: ' . $detail->shape;
            }
            if ($detail->color && shape::hasManyColors($detail->id_shape)) {
                $name .= ' . اللون: ' . $detail->color;
            }
            if ($detail->size && color::hasManyColors($detail->color_id)) {
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
       $order=orderOperations::newOrder($req);
       if($order)
       return response()->json(['suucess'=>true]);
       return response()->json(['suucess'=>false]);

        

    }

    public function isSyncing()
    {
        return event::where('label', 'tawsilix refreshing')->orderby('created_at', 'desc')->first();
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
            [EnumsOrderStatus::$readyToDeliver => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$readyToDeliver)->count()],
            [EnumsOrderStatus::$receivedByDelivery => order::whereIn('id_landing_page', $landingsId)->where('status', EnumsOrderStatus::$receivedByDelivery)->count()],
        ];
        return response($statistcs);

    }

    public function getOrders(Request $req)
    {

        $store = $req->cookie(constants::$storeCookieName);
        $landingsId = landingPage::ofStore($store)->pluck('id');
        $orders = order::whereIn('id_landing_page', $landingsId)->where(function ($query) use ($req) {
            if ($req->search) {
                return $query->where('name', 'like', '%' . $req->search . '%')->orwhere('phone', 'like', '%' . $req->search . '%')->orwhere('city', 'like', '%' . $req->search . '%');
            }

        })->where(function ($query) use ($req) {
            if ($req->status) {
                return $query->whereIn('status', explode(',', $req->status));
            }
        })->NotDeleted()->orderby('created_at', 'desc')->paginate(20);
        return response($orders);
    }
    public function getDelayedTotoday(Request $req)
    {
        $store = $req->cookie(constants::$storeCookieName);
        $landingsId = landingPage::ofStore($store)->pluck('id');

        $orders = order::whereIn('id_landing_page', $landingsId)
            ->wherein('status', [EnumsOrderStatus::$delayed, EnumsOrderStatus::$callRequested])
            ->wheredate('status_date', DB::raw('Date(now())'))
            ->NotDeleted()
            ->orderby('created_at', 'desc')->get();
        return response($orders);

    }

    public function getOrder(Request $req, $id)
    {
        $storeId = $req->cookie(constants::$storeCookieName);
        $order = Order::id($id)->NotDeleted()->first();

        $orderStoreId = $order->store();

        if (!$storeId || !$order || $storeId != $orderStoreId || !JWTAuth::user()->hasAccess($orderStoreId)) {
            return response(['notFound' => true], );
        }

        $details = detail::leftjoin('shapes', 'shapes.id', 'details.id_shape')->leftjoin('landing_pages', 'shapes.id_landing_page', 'landing_pages.id')->leftjoin('sizes', 'sizes.id', 'details.id_size')->leftjoin('colors', 'colors.id', 'details.id_color')->leftjoin('offers', 'offers.id', 'details.id_offer')->where('id_order', $order->id)->get(DB::raw('shapes.name shape , offers.label offer ,sizes.label size ,colors.name color ,offers.id offerId ,colors.id colorId,price ,landing_pages.product_name name'));
        foreach ($details as $key => $detail) {

            if ($detail->offerId && $detail->colorId) {
                $detail->image = hasOffer::join('files', 'files.id', 'has_offers.id_image')->where('id_offer', $detail->offerId)->value('path');
            } else if ($detail->colorId) {
                $detail->image = color::join('files', 'files.id', 'colors.id_image')->where('colors.id', $detail->colorId)->value('path');
            }

        }
        return response(compact('order', 'details'), 200);

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
        return orderChange::leftjoin('users', 'users.id', 'order_changes.id_user')->leftjoin('files', 'users.id_avatar', 'files.id')->orderby('order_changes.created_at', 'desc')->oforder($id)->get(DB::raw('order_changes.status,note,users.name,email,order_changes.created_at date ,to_date ,url avatar'));
    }

    public function delete(Request $request)
    {
        order::wherein('id', $request->ids)->update(['status' => EnumsOrderStatus::$deleted]);
        return res('success', 'orders successfully deleted ', true);
    }
    public function deleteOrder(int $id)
    {
        $delete = order::id($id)->delete();
        if ($delete) {
            return true;
        }
        return false;

    }

}