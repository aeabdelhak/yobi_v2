<?php

namespace App\Http\Controllers;

use App\Models\file;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public static function store($file)
    {

        $name = $file->getClientOriginalName();
        $type = $file->getClientOriginalExtension();

        $path = $file->store('public/files');
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

        $exist = Storage::exists($path);
        if (!$exist) {
            return false;
        }

        if ($file->delete()) {
            Storage::delete($path);
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