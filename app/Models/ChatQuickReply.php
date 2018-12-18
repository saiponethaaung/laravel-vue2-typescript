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

    public static function boot() {
        parent::boot();

        static::deleting(function($qr) {
            foreach($qr->blocks as $block) {
                $block->delete();
            }
        });
    }

    public function attribute()
    {
        return $this->hasOne('App\Models\ChatAttribute', 'id', 'attribute_id');
    }

    public function blocks()
    {
        return $this->hasMany('App\Models\ChatQuickReplyBlock', 'quick_reply_id', 'id');
    }

    public function content()
    {
        return $this->hasOne('App\Models\ChatBlockSectionContent', 'id', 'content_id');
    }
}
