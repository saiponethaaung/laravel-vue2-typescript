<?php

namespace App\Traits\Format;

trait ChatHistoryFormat 
{
    public function formatChat(\App\Models\ProjectPageUserChat $chat)
    {
        return [
            'id' => $chat->id,
            'contentType' => $chat->content_type,
            'mesg' => $chat->mesg,
            'isLive' => $chat->is_live,
            'isSend' => $chat->is_send,
            'fromPlatform' => $chat->from_platform,
            'createdAt' => $chat->created_at
        ];
    }
}