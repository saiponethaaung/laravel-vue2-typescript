<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastFilters extends Model
{
    protected $table = 'project_broadcast_filters';

    protected $fillable = [
        'project_broadcast_id',
        'project_user_segments_id',
        'filter_type',
        'user_attribute_type',
        'user_attribute_value',
        'system_attribute_type',
        'system_attribute_value',
        'chat_attribute_id',
        'chat_attribute_value',
        'condition',
        'chain_condition'
    ];

    public function attribute()
    {
        return $this->hasOne('App\Models\ChatAttribute', 'id', 'chat_attribute_id');
    }

    public function segment()
    {
        return $this->hasOne('App\Models\Segment', 'id', 'project_user_segment_id');
    }
}
