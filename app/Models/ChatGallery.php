<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGallery extends Model
{
    protected $table = 'chat_gallery';

    protected $fillable = [
        'title',
        'sub',
        'image',
        'url',
        'type',
        'order',
        'content_id'
    ];

    public function content()
    {
        return $this->hasOne('App\Models\ChatBlockSectionContent', 'id', 'content_id');
    }
}
