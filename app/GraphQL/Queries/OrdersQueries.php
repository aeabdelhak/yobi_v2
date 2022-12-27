<?php

namespace App\GraphQL\Queries;

use App\Http\operations\orderOperations;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

final class OrdersQueries
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }

    public function statistics($rootValue, array $args){
        $idStore=$args['id_store'];
        return orderOperations::getStatistics($idStore);
    
    }
    public function orders($rootValue, array $args){
        $first=$args['first'];
        $page=$args['page'];
        array_key_exists('first', $args);
        $status=array_key_exists('status', $args) ? $args['status']:null;
        $search=array_key_exists('search', $args) ? $args['search']:null;
        $payload = Auth::parseToken()->getPayload();
        $storeId=$payload->get('storeId');
        $data= orderOperations::orders($storeId,$first,$page,$status,$search);
        return $data;
    
    }


}
