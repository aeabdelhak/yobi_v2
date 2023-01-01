<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'description',
    ];

    static function getId($code){
        return permission::where('code',$code)->value('id');
    }

}