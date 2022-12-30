<?php

namespace App\GraphQL\Queries;

use App\Enums\userRoles;
use App\Models\store;
use App\Models\User;
use App\Types\authResponse;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

final class Authenticate
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function login($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $response = new authResponse();
        $credentials = Arr::only($args, ['email', 'password']);
        Auth::factory()->setTTL(2);
        $token = Auth::attempt($credentials);
        if (!$token) {
            $response->status = 0;
        } else if (!Auth::user()->active) {
            $response->status = 2;
        } else {
            $user = Auth::user();
            $stores = $user->isAdmin() ? store::all() : $user->stores;
            $response->stores = $stores;
            $response->status = 1;
            $response->user = Auth::user();

            if ((count($stores) == 0 || count($stores) == 1) && $user->isAdmin()) {
                Auth::factory()->setTTL(60*6);
                $token = Auth::fromUser($user);
            }
        }
        $response->token = $token;

        return $response;

    }
    public function users()
    {
        return User::where('role', '!=', userRoles::$superAdmin)->get();
    }
    public function initialise()
    {
        $payload = Auth::parseToken()->getPayload();
        $user=Auth::user();
        $storeId=$payload->get('storeId');
        return  [
            "user"=>User::where('id',$user->id)->with(['avatar','abilities'])->first(),
            "store"=>Store::where('id',$storeId)->with(['icon'])->first()
        ];

    }
}
