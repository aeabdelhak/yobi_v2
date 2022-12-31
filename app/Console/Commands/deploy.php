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
        if(env('APP_ENV')=='production'){
                exec('git pull');
                exec('sudo chgrp -R www-data storage bootstrap/cache');
                exec('sudo chmod -R ug+rwx storage bootstrap/cache'); 
                exec('composer update');
                exec('composer install --optimize-autoloader --no-dev');
                exec('php artisan migrate --force');
      }
        exec('php artisan config:cache');
        exec('php artisan route:cache');
        exec('nginx -s reload');
        return Command::SUCCESS;
    }
}
