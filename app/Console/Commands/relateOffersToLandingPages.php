<?php

namespace App\Console\Commands;

use App\Models\hasOffer;
use App\Models\offer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class relateOffersToLandingPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:landing';

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
        $all = hasOffer::join('colors', 'colors.id', 'has_offers.id_color')->join('shapes', 'shapes.id', 'colors.id_shape')->get(DB::raw('shapes.id,id_landing_page'));

        foreach ($all as $key => $offer) {
            offer::where('id', $offer->id)->update(['id_landing_page' => $offer->id_landing_page]);
            echo $key . ' ----> done ';
        }
        return Command::SUCCESS;
    }
}