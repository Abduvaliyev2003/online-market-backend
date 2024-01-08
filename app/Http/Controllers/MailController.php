<?php

namespace App\Http\Controllers;

use App\Mail\DemoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function index()
    {
        $mailData = [
            'title' => 'Mail from Asad',
            'body' => 'This is for testing email usign smtp',
        ];

        Mail::to('aadbuvaliyev@gmail.com')->send(new DemoModel($mailData));

        dd('Email send successfully.');
    }
}
