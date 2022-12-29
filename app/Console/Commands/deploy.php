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
        exec('
        sudo chgrp -R www-data storage bootstrap/cache
        sudo chmod -R ug+rwx storage bootstrap/cache
        composer install --optimize-autoloader --no-dev
        php artisan config:cache
        php artisan route:cache
        nginx -s reload
        ');
        return Command::SUCCESS;
    }
}
