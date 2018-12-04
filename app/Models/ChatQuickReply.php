<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatQuickReply extends Model
{
    protected $table = 'chat_quick_reply';

    protected $fillable = [
        'title',
        'attribute_id',
        'content_id',
        'value'
    ];

    public function attribute()
    {
        return $this->hasOne('App\Models\ChatAttribute', 'id', 'attribute_id');
    }
}
