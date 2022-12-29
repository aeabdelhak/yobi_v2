<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\image;

final class ImageMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function newImage($_s, array $args)
    {
        $image = new image();
        $image->id_landing_page = $args['id_landing_page'];
        $image->id_image = FilesController::store($args['image']);
        if ($image->save()) {
            $image->refresh();
            $image->file;
                return $image; 
        }        
    }
    public function delete($_, array $args){
        $image = image::find($args['id']);
        if (!$image) {
            return false;
        }
        $id_file = $image->id_file;

        if ($image->delete()) {
            return FilesController::delete($id_file);
        }
        return false;
    }
}
