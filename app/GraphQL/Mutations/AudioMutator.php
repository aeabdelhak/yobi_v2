<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\audio;
use Illuminate\Support\Facades\DB;

final class AudioMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }

    public function newAudio($_, array $args)
    {
        $audio = new audio();
        $audio->owner = $args['owner'];
        $audio->id_file = FilesController::store($args['audio']);
        $audio->id_landing_page = $args['id_landing_page'];
        if ($audio->save()) {
            $audio->refresh();
            $audio->file;
                return $audio; 
        }
    }
    public function delete($_, array $args){
        $audio = audio::find($args['id']);
        if (!$audio) {
            return false;
        }
        $id_file = $audio->id_file;
        if ($audio->delete()) {
            return FilesController::delete($id_file);
        }
        return false;
    }

    public function changeOwnerName($_, array $args)
    {
        $audio = audio::where('id',$args['id'])->first();
        if(!$audio){
            return null;
        }

        $audio->owner=$args['name'];
        $audio->save();
        $audio->file;
        return $audio;


    }
    

}