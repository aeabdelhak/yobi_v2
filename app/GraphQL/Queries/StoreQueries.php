<?php

namespace App\GraphQL\Queries;

use App\Enums\userRoles;
use App\Models\store;
use App\Models\User;
use App\Types\authResponse;
use App\Types\choseStoreRespsonse;
use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

final class StoreQueries
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function choseStore($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $storeId=$args['storeId'];    
        $user=Auth::user();
        $response=new choseStoreRespsonse();
        if($user->hasAccess($storeId)){
            Auth::factory()->setTTL(2*60);
            Auth::customClaims([
                'storeId'=>$storeId,
                'permissions' => $user->getPermissions()
            ]);
            $response->token = Auth::fromUser($user);   
            $response->status =1;   
        }
        else {
            $response->status =0; 
        }
        return $response;
    }

}