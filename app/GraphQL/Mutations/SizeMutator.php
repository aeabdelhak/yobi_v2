<?php

namespace App\GraphQL\Mutations;

use App\Models\size;

final class SizeMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function toggleState($_, array $args){
        $size = size::find($args['id']);
        $size->status=$size->status==0 ? 1:0;
        $size->save();
 
        return $size;
    }
}
