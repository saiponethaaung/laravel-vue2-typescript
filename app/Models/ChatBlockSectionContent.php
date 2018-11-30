<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlockSectionContent extends Model
{
    protected $table = 'chat_block_section_content';

    protected $fillable = [
        'section_id',
        'order',
        'type',
        'text',
        'content',
        'image',
        'duration'
    ];

    public function section()
    {
        return $this->hasOne('App\Models\ChatBlockSection', 'id', 'section_id');
    }
}
