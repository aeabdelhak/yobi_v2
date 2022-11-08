<?php

namespace App\Console\Commands;

use App\Http\Controllers\orderController;
use App\Models\order;
use Illuminate\Console\Command;

class updatePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $start = time();
        echo "start \n";

        foreach (order::get() as $key => $order) {

            order::id($order->id)->update(['total_paid' => (new orderController)->getTotalPrice($order->id)]);
            echo $order->id . "done \n";

        }
        $diff = time() - $start;
        echo "ended $diff s  \n";

        return Command::SUCCESS;
    }
}