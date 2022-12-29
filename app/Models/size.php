<?php

namespace App\Models;

use App\Enums\sharedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class size extends Model
{
    use HasFactory,SoftDeletes;
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
    public function scopeActive($query)
    {
        $query->where('status', sharedStatus::$active);
    }
    public function scopeId($query, $id)
    {
        $query->where('id', $id);
    }
}