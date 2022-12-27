<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class orderChange extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'status',
        'to_date',
        'note',
        'id_order',
        'created_at',
    ];

    public static function boot()
    {

        parent::boot();

        static::creating(function ($model) {
            if (JWTAuth::check()) {
                $model->id_user = JWTAuth::user()->id;
            }

        });

    }

    public function scopeOforder($query, $id)
    {
        return $query->where('id_order', $id);
    }

    public function order(){
        return $this->belongsTo(order::class,'id_order');
    }
    public function admin(){
        return $this->belongsTo(User::class,'id_user');
    }

}