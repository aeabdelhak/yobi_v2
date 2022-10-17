<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class card extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'title',
        'status',
        'body',
        'id_landing_page',
    ];
    public function landing()
    {
        $this->belongsTo(landingPage::class);
    }
    public function scopeOfLanding($query, $id)
    {
        $query->where('id_landing_page', $id);
    }

}