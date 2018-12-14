<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatButton extends Model
{
    protected $table = 'chat_button';

    protected $fillable = [
        'title',
        'text',
        'phone',
        'url',
        'content_id',
        'gallery_id',
        'action_type',
        'order'
    ];
}
