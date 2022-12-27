<?php

namespace App\Exports;

use App\Models\order;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $req = Request();
        $id = $req->id;
        return order::join('landing_pages', 'id_landing_page', 'landing_pages.id')->join('stores', 'id_store', 'stores.id')->
            where('stores.id', $id)
            ->get(DB::raw('orders.name,orders.status,phone,product_name,city,address,orders.id'));
    }

    public function headings(): array
    {
        return ['id', 'name', 'city', 'address', 'phone', 'landing page', 'status'];
    }
    public function map($order): array
    {
        return [
            $order->id,
            $order->name,
            $order->city,
            $order->address,
            $order->phone,
            $order->product_name,
            statusToString($order->status),
        ];
    }

}