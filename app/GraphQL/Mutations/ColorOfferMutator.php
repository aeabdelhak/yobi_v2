<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\hasOffer;

final class ColorOfferMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function toogleStatus($_, array $args)
    {
        $hasOffer = hasOffer::find($args['id']);
        $hasOffer->status=$hasOffer->status==0 ? 1:0;
        $hasOffer->save();
 
        return $hasOffer;
    }
    public function changeImage($_, array $args)
    {
        $hasOffer = hasOffer::find($args['id']);
        $id_file = $hasOffer->id_image;
        $hasOffer->id_image=FilesController::store($args['image']);
        if($hasOffer->save()){
            FilesController::delete($id_file);
        }
        $hasOffer->refresh();
        return $hasOffer->image;
    }
    public function assign($_, array $args)
    {
        $hasOffer = new hasOffer();
        $hasOffer->id_image=FilesController::store($args['image']);
        $hasOffer->id_color=$args['id_color'];
        $hasOffer->id_offer=$args['id_offer'];
        $hasOffer->refresh();
        $hasOffer->image;
        $hasOffer->offer;
        return $hasOffer;
    }
}
