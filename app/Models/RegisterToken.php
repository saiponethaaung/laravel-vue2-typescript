<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterToken extends Model
{
    protected $table = 'register_token';

    protected $fillable = [
        'user_id',
        'status',
        'token',
        'wrong_attempted'
    ];
}
