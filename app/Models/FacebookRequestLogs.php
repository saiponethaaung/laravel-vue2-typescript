<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookRequestLogs extends Model
{
    protected $table = 'facebook_request_logs';

    protected $fillable = [
        'data',
        'is_echo',
        'is_read',
        'is_deliver',
        'fb_request',
        'fb_response',
        'is_income',
        'is_payload',
    ];
}
