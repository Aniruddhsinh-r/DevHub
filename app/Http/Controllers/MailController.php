<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeBackMail;

class MailController extends Controller
{
    public function sendMail($to, $msg, $subject) {
        Mail::to($to)->send(new WelcomeBackMail($msg, $subject));
    }
}
