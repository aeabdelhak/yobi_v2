<?php

namespace App\Models;

use App\Http\Controllers\FilesController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_image',
        'id_landing_page',
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($image) { // before delete() method call this
             FilesController::delete($image->id_image);
        });
    }

    public function file()
    {
        return $this->hasOne(file::class,'id','id_image');
    }
    public function landing()
    {
        return $this->hasOne(landingPage::class,'id','id_landing_page');
    }
    
}