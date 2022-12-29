<?php

namespace App\Http\Controllers;

use App\Models\file;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public static function store($file)
    {

        $type = $file->getClientOriginalExtension();
        $fileName = pathinfo($file,PATHINFO_FILENAME).time();
        $name = $fileName.$type;
        $path = Storage::disk('cdn')->put('',$file);
        $save = new file();
        $save->name = $name;
        $save->type = $type;
        $save->path = $path;
        $save->url = Storage::url($path);
        
        $save->save();
        return $save->id;

    }
    public static function delete($id)
    {
        $file = file::find($id);
        if (!$file) {
            return false;
        }

        $path = $file->path;

        $exist = Storage::disk('cdn')->exists($path);
        if (!$exist) {
            return false;
        }

        if ($file->delete()) {
            Storage::disk('cdn')->delete($path);
            return true;
        }

    }

    public function decode64AndSave($base64)
    {
        $decoded = base64_decode($base64);

        return $this->store($decoded);

    }
    public function download(Request $req)
    {
        return Storage::download($req->path);
    }
    public static function path($id)
    {
        return file::where('id', $id)->value('path');
    }
    public static function url($id)
    {
        return file::where('id', $id)->value('url') ?? null;
    }

}