<?php

namespace App\Models;

use App\Enums\permissions;
use App\Enums\userRoles;
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
    public function controle(User $user): bool
    {
        return true;
        return in_array(permissions::$landing, $user->Permissions()) ?? $user->role==userRoles::$superAdmin ;
    }


}