<?php

namespace App\Console\Commands;

use App\Enums\orderStatus;
use App\Models\order;
use Illuminate\Console\Command;

class softDeleteOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:softdelete';

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
        order::where('status',orderStatus::$deleted)->delete();

        return Command::SUCCESS;
    }
}
