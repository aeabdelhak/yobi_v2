<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detail extends Model
{
    use HasFactory;
    protected $fillable = [
        'label',
        'description',
        'started_at',
        'ended_at',
        'status',
        'id_user',

    ];

}