<?php

namespace App\Models;

use App\Enums\sharedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class size extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'label',
        'status',
        'id_shape',
        'id_color',

    ];

    public function scopeOfColor($query, $id)
    {
        $query->where('id_color', $id)->where('status', '!=', sharedStatus::$deleted);
    }
}