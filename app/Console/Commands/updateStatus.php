<?php

namespace App\Console\Commands;

use App\Http\Controllers\tawsilixController;
use Illuminate\Console\Command;

class updateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tawsilix:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update orders status ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        (new tawsilixController())->updateOrderStatus();
        return Command::SUCCESS;
    }
}