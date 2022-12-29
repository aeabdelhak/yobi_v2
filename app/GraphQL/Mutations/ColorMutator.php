<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\color;

final class ColorMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function newColor($_s, array $args)
    {
        $color = new color();
        $color->id_shape = $args['id_shape'];
        $color->name = $args['name'];
        $color->color_code = $args['color_code'];
        $color->id_image = FilesController::store($args['image']);
        if ($color->save()) {
            $color->refresh();
            $color->image;
                return $color; 
        }        
    }
    public function delete($_, array $args){
        $color = color::find($args['id']);
        if (!$color) {
            return false;
        }
        $id_file = $color->id_image;

        if ($color->delete()) {
            return FilesController::delete($id_file);
        }
        return false;
    }
    public function toggleState($_, array $args){
        $color = color::find($args['id']);
        $color->status=$color->status==0 ? 1:0;
        $color->save();
        $color->refresh();
        $color->image;
        return $color;
    }
    public function editColor($_, array $args){
        $color = color::find($args['id']);
        if (!$color) {
            return null;
        }
        $id_file = $color->id_image;
        if(isset($args['image'])){
            $color->id_image=FilesController::store($args['image']);
        }
        if(isset($args['name'])){
            $color->name=$args['name'];
        }
        if(isset($args['color_code'])){
            $color->color_code=$args['color_code'];
        }
        if ($color->save()) {
            if($id_file!==$color->id_image)
            FilesController::delete($id_file);
        }
        $color->refresh();
        $color->image;
        return $color;
    }
 
}
