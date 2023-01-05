<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'deploy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        exec('composer update');
        exec('composer install --optimize-autoloader --no-dev');
        exec('php artisan migrate --force');
        exec('php artisan config:cache');
        exec('php artisan route:cache');

        return Command::SUCCESS;
    }
}
