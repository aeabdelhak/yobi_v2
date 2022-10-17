<?php

namespace App\Http\Controllers;

use App\Mail\welcome;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailController extends Controller
{

    public function welcome($email, $password)
    {
        try {
            Mail::to($email)->send(new welcome($password));
            return true;
        } catch (Throwable $e) {
            return false;
        }

    }
}