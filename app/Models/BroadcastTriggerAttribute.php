<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Eloquent\HaveBroadcast;

class BroadcastTriggerAttribute extends Model
{
    use HaveBroadcast;

    protected $table = 'project_broadcast_trigger_attribute';

    protected $fillable = [
        'project_broadcast_id',
        'chat_attribute_id',
        'condition',
        'value'
    ];

    public function attrValue()
    {
        return $this->hasOne('App\Models\ChatAttribute', 'id', 'chat_attribute_id');
    }

    public function projectBroadcast()
    {
        return $this->hasOne('App\Models\broadcast', 'id', 'project_broadcast_id');
    }
}
