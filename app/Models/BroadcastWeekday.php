<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastWeekday extends Model
{
    protected $table = 'project_broadcast_weekday';

    protected $fillable = [
        'project_broadcast_id',
        'days',
        'status'
    ];
}
