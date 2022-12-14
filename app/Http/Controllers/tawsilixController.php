<?php

namespace App\Http\Controllers;

use App\Enums\eventStatus;
use App\Enums\orderStatus;
use App\Enums\sharedStatus;
use App\Models\event;
use App\Models\order;
use App\Models\orderChange;
use App\Models\shippServices;
use App\Models\store;
use Illuminate\Support\Facades\DB;

class tawsilixController extends Controller
{

    public function cities()
    {
        $path = '/cities.php';
        return $this->callapi($path);

    }

    public function checkStatus($id)
    {
        echo "-check for $id \n";
        echo "      ram usage " . round(memory_get_usage() / 1024 / 1024) . " MB \n";

        $time = time();

        $shipp = shippServices::where('id_shipping', $id)->first();

        $res = $this->callapi('/track.php?code=' . $id, true);
        $order = order::id($shipp->id_order)->first();
        $status = $order->status;

        if ($res["0"]['state'] == 'Livré') {
            $status = orderStatus::$delivered;
            $shipp->status = sharedStatus::$inActive;
            $shipp->save();
        }
        if ($res["0"]['state'] == 'Reçu par livreur') {
            $status = orderStatus::$receivedByDelivery;
        }
        if ($res["0"]['state'] == 'Expédié') {
            $status = orderStatus::$shipping;
        }
        if ($res["0"]['state'] == 'Prèt pour expédition') {
            $status = orderStatus::$readyToDeliver;
        }
        if ($res["0"]['state'] == 'Ramassé') {
            $status = orderStatus::$collected;
        }
        if ($res["0"]['state'] == 'En attente de ramassage') {
            $status = orderStatus::$pushedToDelivery;
        }
        if ($res["0"]['state'] == 'Retour client reçu') {
            $status = orderStatus::$returned;
            $shipp->status = sharedStatus::$inActive;
            $shipp->save();
        }
        if ($order->status !== $status) {
            $order->status = $status;
            $order->save();

            $orderChange = new orderChange();
            $orderChange->id_order = $order->id;
            $orderChange->status = $status;
            $orderChange->save();
        }
        $diff = time() - $time;
        echo "      ended within: $diff  \n";

    }

    public function push(
        $id,
        $fullName,
        $city,
        $address,
        $phone,
        int $idOrder,
        $product,
        int $qty,
        $note,
        int $change,
        int $openpackage,
        $price) {

        $tokens = store::join('landing_pages', 'landing_pages.id_store', 'stores.id')->join('orders', 'orders.id_landing_page', 'landing_pages.id')->where('orders.id', $idOrder)->first(['token', 'secret_token']);

        $data = array(
            'tk' => $tokens->token,
            'sk' => $tokens->secret_token,
            'fullname' => $fullName,
            'phone' => $phone,
            "city" => $city,
            "address" => $address,
            "price" => $price,
            "product" => $product,
            "qty" => $qty,
            "note" => $note ?? '..',
            "change" => $change,
            "openpackage" => $openpackage,
        );

        $path = '/addcolis.php?' . http_build_query($data);

        $req = $this->callapi($path);
        if (isset($req->code)) {
            $code = $req->code;
            shippServices::create([
                'id_order' => $id,
                'id_shipping' => $code,
                'by' => 'tawsilix',
                'status' => sharedStatus::$active,

            ]);
            return true;
        }
        return false;

    }

    private function callapi($path, $params = null)
    {

        return json_decode(curl('GET', 'https://tawsilex.ma' . $path), $params);

    }

    public function updateOrderStatus()
    {
        $event = event::where('label', 'tawsilix refreshing')->where('status', eventStatus::$new)->first();
        if ($event) {
            return;
        }
        $event = new event();
        $event->label = 'tawsilix refreshing';
        $event->started_at = DB::raw('CURRENT_TIMESTAMP');
        $event->save();
        $orders = shippServices::join('orders', 'orders.id', 'shipp_services.id_order')->where('shipp_services.status', sharedStatus::$active)->get(DB::raw('id_shipping,id_order,orders.status,shipp_services.id'));
        $status = eventStatus::$success;
        $event->refresh();
        try {
            foreach ($orders as $key => $order) {
                $this->checkStatus($order->id_shipping);
            }
        } catch (\Throwable$th) {
            $status = eventStatus::$error;
        }

        $event->ended_at = DB::raw('CURRENT_TIMESTAMP');
        $event->status = $status;

        $event->save();

    }

}