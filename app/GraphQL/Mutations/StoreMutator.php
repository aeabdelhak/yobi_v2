<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\deployController;
use App\Http\Controllers\FilesController;
use App\Models\store;
use Illuminate\Support\Facades\DB;

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
        $store = store::find($args['id']);
        $id_file = $store->id_logo;
        $store->id_logo = FilesController::store($args['image']);
        if ($store->save()) {
            FilesController::delete($id_file);
        }
        $store->refresh();
        return $store->icon;
    }
    public function changeDomain($_, array $args)
    {
        DB::beginTransaction();
        $status = 0;
        $store = store::find($args['idStore']);
        if ($store) {
            $domain = $args['domain'];
            $ip = gethostbyname('check.' . $domain);
            $verified = false;
            if ($ip !== '89.117.37.24') {
                $status = 2;
            } else {
                if (deployController::generateWildcardSSl($domain)) {
                    $landings = $store->landings()->withTrashed()->get();
                    deployController::undeployStore($store);
                    foreach ($landings as $key => $$landing) {
                        deployController::undeployLanding($landing);
                    }
                    $store->domain = $domain;
                    $store->save();
                    $store->refresh();
                    deployController::deployStore($store);
                    foreach ($store->landings as $key => $$landing) {
                        deployController::deployLanding($landing);
                    }
                    $status = 1;
                }
                else 
                    $status = 2;
            }
        }

        $store->refresh();

        return compact(['status', 'store']);

    }
    public function update($_, array $args)
    {
        $store = store::find($args['id']);
        $store->name = $args['name'];
        $store->token = $args['token'];
        $store->secret_token = $args['secret_token'];
        $store->facebook = array_key_exist_or('facebook', $args, null);
        $store->google = array_key_exist_or('google', $args, null);
        $store->tiktok = array_key_exist_or('tiktok', $args, null);
        $store->fecebook_meta_tag = array_key_exist_or('fecebook_meta_tag', $args, null);
        $store->description = $args['description'];
        $store->icon;
        $store->users;
        $store->save();
        return $store;
    }

}
