<?php

namespace App\Http\Controllers;

use App\Enums\permissions;
use App\Models\audio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class audioController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth:api');
        $this->middleware('permission:' . permissions::$landing, ['only' => ['newAudio', 'edit', 'delete']]);

    }

    public function newAudio(Request $req)
    {
        $audio = new audio();
        $audio->owner = $req->owner;
        $audio->id_file = FilesController::store($req->audio);
        $audio->id_landing_page = $req->id_landing_page;
        if ($audio->save()) {
            $audio = audio::leftjoin('files', 'files.id', 'id_file')->where('audio.id', $audio->id)->first(DB::raw('owner,audio.id,url,path'));
            return response()->json([
                'status' => "Success",
                'data' => $audio,
            ]);
        }

    }
    public function delete(Request $req)
    {
        $audio = audio::find($req->id);
        if (!$audio) {
            return false;
        }
        $id_file = $audio->id_file;

        if ($audio->delete()) {
            return FilesController::delete($id_file);
        }

    }
    public function edit(Request $req)
    {
        try {

            $audio = audio::findorfail($req->id);
        } catch (Throwable $e) {
            return response(null, 404);
        }

        $audio->owner = $req->owner;
        if ($audio->save()) {
            return response()->json(['status' => 'Success', 'data' => $audio]);
        }

    }
}