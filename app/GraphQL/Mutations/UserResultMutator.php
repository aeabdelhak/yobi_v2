<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\userResult;

final class UserResultMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    
    }
    public function newResult($_s, array $args)
    {
        $userResult = new userResult();
        $userResult->id_landing_page = $args['id_landing_page'];
        $userResult->id_image = FilesController::store($args['image']);
        if ($userResult->save()) {
            $userResult->refresh();
            $userResult->image;
                return $userResult; 
        }        
    }
}
