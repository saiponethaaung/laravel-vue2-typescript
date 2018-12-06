<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatUserInput extends Model
{
    /**
     * 
     * Validation
     * 
     * null none
     * 1 Phone
     * 2 Email
     * 3 Number
     * 
     */
    protected $table = 'chat_user_input';

    protected $fillable = [
        'question',
        'content_id',
        'validation',
        'attribute_id'
    ];

    public function attribute()
    {
        return $this->hasOne('App\Models\ChatAttribute', 'id', 'attribute_id');
    }

    public function content()
    {
        return $this->hasOne('App\Models\ChatBlockSectionContent', 'id', 'content_id');
    }
}
