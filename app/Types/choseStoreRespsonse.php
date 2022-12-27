<?php

namespace App\Types;

use App\Models\store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class choseStoreRespsonse
{
    public int $status;
    public string $token;
    public User  $user ;
}