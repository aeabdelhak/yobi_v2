<?php

namespace App\GraphQL\Queries;

use App\Models\landingPage;

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
}
