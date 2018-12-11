<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookRequestLogs extends Model
{
    protected $table = 'facebook_request_logs';

    protected $fillable = [
        'data'
    ];
}
