<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttribute extends Model
{
    protected $table = 'chat_attribute';

    /**
     * 
     * Type
     * 
     * 0 no type
     * 1 phone
     * 2 email
     * 
     */
    protected $fillable = [
        'attribute',
        'type',
        'is_system',
        'project_id'
    ];

    public function chatValue() {
        return $this->hasMany('App\Models\ProjectPageUserAttribute', 'attribute_id', 'id');
    }

    public function chatUserInput() {
        return $this->hasMany('App\Models\ChatUserInput', 'attribute_id', 'id');
    }

    public function chatQuickReply() {
        return $this->hasMany('App\Models\ChatQuickReply', 'attribute_id', 'id');
    }

    public function broadcastTrigger() {
        return $this->hasMany('App\Models\BroadcastTriggerAttribute', 'chat_attribute_id', 'id');
    }
}
