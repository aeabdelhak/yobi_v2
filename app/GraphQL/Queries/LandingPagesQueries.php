<?php

namespace App\GraphQL\Queries;

use App\Models\landingPage;
use App\Models\store;
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
        $domain = $args['domain'];
        $landing = landingPage::where('landing');

    }
    public function storeLandingsPages($_, array $args)
    {
        $store = JWTAuth::user()->store();
        return landingPage::where('id_store', $store->id)->get();
    }

    public function landingForClient($_, array $args)
    {

        $domain = $args['domain'];
        $arr = explode('.', $domain);
        $count = count($arr);
        if ($count == 2) {
            return null;
        } 

            $storeDomain = implode('.', [$arr[$count - 2], $arr[$count - 1]]);
            $landingDomain = substr(str_replace($storeDomain, '', $domain), 0, -1);

            $exist = landingPage::join('stores', 'stores.id', 'landing_pages.id_store')->where('stores.domain', $storeDomain)->where('landing_pages.domain', $landingDomain)->value('landing_pages.id');
            if (!$exist) {
                return null;
            } 

                $landing=landingPage::find($exist);
                return  $landing;
    }
}
