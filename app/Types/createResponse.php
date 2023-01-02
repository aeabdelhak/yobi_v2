<?php

namespace App\Types;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class createResponse
{
    public int $status;
    public ?Model  $data ;

}