<?php

namespace App\GraphQL\Queries;

use App\Http\Controllers\tawsilixController;

final class TawsilexQueries
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function getCities($_, array $args)
    {
      return tawsilixController::cities() ;
    }
}
