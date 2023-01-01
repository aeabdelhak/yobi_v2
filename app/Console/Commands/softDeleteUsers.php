<?php

namespace App\Console\Commands;

use App\Enums\userStatus;
use App\Models\User;
use Illuminate\Console\Command;

class softDeleteUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:softDelete';

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
       User::where('active',userStatus::$deleted)->delete();

        return Command::SUCCESS;
    }
}