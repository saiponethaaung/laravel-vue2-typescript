<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $table = 'project_broadcast';

    /**
     * 
     * Broadcast type
     * 
     * 1: send now
     * 2: trigger
     * 3: schedule
     * 
     * 
     * Interval type
     * 
     * 1: no repeat
     * 2: daily
     * 3: weekend
     * 4: every month
     * 5: workdays
     * 6: yearly
     * 7: custom
     * 
     */

    protected $fillable = [
        'project_id',
        'day',
        'month',
        'year',
        'time',
        'interval_type',
        'duration',
        'duration_type',
        'project_message_tag_id',
        'trigger_type',
        'broadcast_type',
        'status',
        'complete'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function weekday()
    {
        return $this->hasMany('App\Models\BroadcastWeekday', 'project_broadcast_id', 'id');
    }
}
