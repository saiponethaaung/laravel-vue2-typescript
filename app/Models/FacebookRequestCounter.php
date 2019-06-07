<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookRequestCounter extends Model
{
    protected $table = 'facebook_request_counter';

    protected $fillable = [
        'section',
        'request'
    ];
}
