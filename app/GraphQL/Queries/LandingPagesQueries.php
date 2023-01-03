<?php

namespace App\GraphQL\Queries;

use App\Models\landingPage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

final class LandingPagesQueries
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }

    public function getForClient($_, array $args)
    {
        $domain=$args['domain'];
        $landing=landingPage::where('landing');
        
    }
    public function storeLandingsPages($_, array $args)
    {
        $store=JWTAuth::user()->store();
       return landingPage::where('id_store',$store->id)->get();
    }
}
