<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatQuickReplyBlock extends Model
{
    protected $table = 'chat_quick_reply_block';

    protected $fillable = [
        'quick_reply_id',
        'section_id'
    ];

    public function value()
    {
        return $this->hasOne('App\Models\ChatBlockSection', 'id', 'section_id');
    }
}
