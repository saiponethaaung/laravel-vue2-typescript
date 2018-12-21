<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\Facebook\Webhook\ProcessWebhook;

class TestController extends Controller
{
    public function startQueue(Request $request)
    {
        ProcessWebhook::dispatch();
    }
}
