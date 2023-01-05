<?php

namespace App\GraphQL\Queries;

use App\Enums\userRoles;
use App\Models\hasPermission;
use App\Models\permission;
use App\Models\store;
use App\Models\User;
use App\Types\authResponse;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
                Auth::factory()->setTTL(60 * 6);
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
    public function storeUsers($_, $args)
    {
        $store = store::find($args['idStore']);
        $store->icon;
        $has_permisson = hasPermission::where('id_store', $store->id)->where('id_user', $args['id']);
        return User::with(['stores', 'avatar'])->where('stores')->get();
    }
    public function storeUser($_, $args)
    {
        $user=null;
        $abilities=null;

        $store = store::find($args['idStore']);
        if (!$store) {
            return null;
        }

        $abilities = hasPermission::join('permissions','permissions.id','has_permissions.id_permission')->where('id_store', $args['idStore'])->where('id_user', $args['id'])->distinct()->get(DB::raw('permissions.id id , code ,description'));
        if (count($abilities)==0) {
            
                return null;
            
        }
        $user =User::with( 'avatar')->where('id',$args['id'])->first();
        if(!$user){
            return null;
        }
        return compact(['user','abilities']) ;
    }
    public function initialise()
    {
        $user = Auth::user();
        $user->abilities;
        $user->avatar;
        $store = $user->store();
        $store->icon;
        if ($user->isAdmin()) {
            $store->users;
        }

        return [
            "user" => $user,
            "store" => $store,
        ];

    }
}
