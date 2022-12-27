<?php

namespace App\Http\operations;

use App\Enums\sharedStatus;
use App\Models\color;
use App\Models\detail as ModelsDetail;
use App\Models\hasOffer;
use App\Models\landingPage;
use App\Models\offer;
use App\Models\order;
use App\Models\shape;
use App\Models\size;
use App\Enums\orderStatus;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

use function GuzzleHttp\json_encode;

class orderOperations
{



    public static function newOrder($nOrder)
    {

        $insertDetails = [];

        $details = $nOrder->details;
        foreach ($details as $key => $detail) {
            $size = null;
            $color = $detail->id_color;
            $shape = $detail->id_shape;
            $offer = null;

            $shapeModel = shape::id($shape)->active()->first();
            if (!$shapeModel) {
                return null;
            }

            if (isset($detail->id_offer)) {
                $offer = hasOffer::joinOffer()->where('id_color', $detail->id_color)->where('has_offers.status', sharedStatus::$active)->where('offers.status', sharedStatus::$active)->first();
                if (!$offer) {
                    return null;
                }
                $offerPrice=$offer->promotioned_price ?? $offer->original_price;
                $offer = $detail->id_offer;

            }
            $colorModel = color::id($color)->active()->first();
            if (!$colorModel) {
                return null;
            }
            if (isset($detail->id_size)) {
                $size = size::id($detail->id_size)->active()->first();
                if (!$size) {
                    return null;
                }
                $size = $detail->id_size;
            }

            $shapePrice=$shapeModel->promotioned_price ?? $shapeModel->original_price;

            $insertDetails[] = [
                'id_color' => $color,
                'id_shape' => $shape,
                'id_offer' => $offer,
                'id_size' => $size,
                'price' => $offerPrice ?? $shapePrice,
                'amount' => $detail->amount,
            ];
        }
        if (count($insertDetails) == 0) {
            return null;
        }

        $order = new order();
        $order->name = $nOrder->name;
        $order->city = $nOrder->city;
        $order->address = $nOrder->address;
        $order->phone = $nOrder->phone;
        $order->id_landing_page = $nOrder->id_landing_page;
        $order->id_store = $nOrder->id_store;
        $order->save();

        $tprice=0;
        $allDetails = [];
        foreach ($insertDetails as $key => $detail) {
            $detail['id_order'] = $order->id;
            $allDetails[] = $detail;
            $tprice+=$detail['price'] * $detail['amount'] ;
        }

        ModelsDetail::insert($allDetails);

        $order->refresh();
        $order->total_paid = $tprice;
        $order->save();

        return $order;

    }


    static   public function getStatistics($storeId)
        {
            $statistcs = [
                ["status"=>orderStatus::$new , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$new)->count()],
                ["status"=>orderStatus::$verified , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$verified)->count()],
                ["status"=>orderStatus::$shipping , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$shipping)->count()],
                ["status"=>orderStatus::$delivered , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$delivered)->count()],
                ["status"=>orderStatus::$canceled , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$canceled)->count()],
                ["status"=>orderStatus::$noResponce , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$noResponce)->count()],
                ["status"=>orderStatus::$callRequested , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$callRequested)->count()],
                ["status"=>orderStatus::$callOv3 , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$callOv3)->count()],
                ["status"=>orderStatus::$voiceMail , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$voiceMail)->count()],
                ["status"=>orderStatus::$delayed , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$delayed)->count()],
                ["status"=>orderStatus::$outOfArea , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$outOfArea)->count()],
                ["status"=>orderStatus::$collected , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$collected)->count()],
                ["status"=>orderStatus::$returned , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$returned)->count()],
                ["status"=>orderStatus::$readyToDeliver , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$readyToDeliver)->count()],
                ["status"=>orderStatus::$receivedByDelivery , "count"=>order::where('id_store',$storeId)->where('status', orderStatus::$receivedByDelivery)->count()],
            ];
            return $statistcs;

        }

       static public function orders(Int $id_store, Int $first ,Int $page ,Array $status = null,string $search=null)
        {

         $from=($page-1) *$first;
            $orders = order::where('id_store', $id_store)->where(function ($query) use ($first,$page,$search) {
                if ($search) {
                    return $query->where('name', 'like', '%' . $search . '%')->orwhere('phone', 'like', '%' . $search . '%')->orwhere('city', 'like', '%' . $search . '%');
                }
    
            })->where(function ($query) use ($status,) {
                if ($status) {
                    return $query->whereIn('status',  $status);
                }
            })->orderby('created_at', 'desc')->skip($from)->take($first)->get();
        
            $total = order::where('id_store', $id_store)->where(function ($query) use ($first,$page,$search) {
                if ($search) {
                    return $query->where('name', 'like', '%' . $search . '%')->orwhere('phone', 'like', '%' . $search . '%')->orwhere('city', 'like', '%' . $search . '%');
                }
    
            })->where(function ($query) use ($status,) {
                if ($status) {
                    return $query->whereIn('status',  $status);
                }
            })->orderby('created_at', 'desc')->count();

            return [
                "data"=>$orders,
                "from"=>$from,
                "to"=>($from)+$orders->count(),
                "total"=>$total,
                "page"=>$page==0 ? 1 : $page,
                "pages"=>ceil($total / $first)
            ];
        }

}