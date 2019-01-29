<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Eloquent\HaveBroadcast;

class BroadcastTriggerAttribute extends Model
{
    use HaveBroadcast;

    protected $table = 'project_broadcast_trigger_attribute';

    protected $fillable = [
        'chat_attribute_id',
        'condition',
        'value'
    ];
}
