<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\store;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

final class StoreMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function changeIcon($_, array $args)
    {
        $store = JWTAuth::user()->store();
        $id_file = $store->id_logo;
        $store->id_logo=FilesController::store($args['image']);
        if($store->save()){
            FilesController::delete($id_file);
        }
        $store->refresh();
        return $store->icon;
    }
    public function update($_, array $args)
    {
        $store = JWTAuth::user()->store();
        $store->name=$args['name'];
        $store->token=$args['token'];
        $store->secret_token=$args['secret_token'];
        $store->facebook= array_key_exist_or('facebook',$args,null);
        $store->google= array_key_exist_or('google',$args,null) ;
        $store->tiktok=array_key_exist_or('tiktok',$args,null);
        $store->fecebook_meta_tag=array_key_exist_or('fecebook_meta_tag',$args,null);
        $store->description=$args['description'];
        $store->icon;
        $store->users;
        $store->save();
        return $store;
    }


}
