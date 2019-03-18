<?php

namespace App\Traits\Format;

trait ChatHistoryFormat 
{
    public function formatChat(\App\Models\ProjectPageUserChat $chat)
    {
        $dateTime = new \DateTime($chat->created_at);
        $dateTime->setTimeZone(new \DateTimeZone('Asia/Yangon'));
        return [
            'id' => $chat->id,
            'contentType' => $chat->content_type,
            'mesg' => $chat->mesg,
            'isLive' => $chat->is_live,
            'isSend' => $chat->is_send,
            'fromPlatform' => $chat->from_platform,
            'createdAt' => $dateTime->format('d M, Y h:i a')
        ];
    }
}