<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class orderAndStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:tostore';

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

        DB::update("UPDATE orders
        SET id_store = (
        SELECT id_store
        FROM landing_pages
        WHERE landing_pages.id = orders.id_landing_page
        );
                ");

        return Command::SUCCESS;
    }
}