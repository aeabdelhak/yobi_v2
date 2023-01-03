<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class file extends Model
{
    protected $fillable = [
        'id',
        'name',
        'path',
        'url',
        'type',
    ];
    use HasFactory;
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($file) {
            $path = $file->path;
            $exist = Storage::exists($path);
            if ($exist) {
                if ($file->delete()) {
                    Storage::delete($path);
                    return true;
                }
            }
        });
    }
}
