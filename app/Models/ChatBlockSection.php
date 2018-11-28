<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlockSection extends Model
{
    protected $table = 'chat_block_section';

    protected $fillable = [
        'block_id',
        'title',
        'order'
    ];
}
