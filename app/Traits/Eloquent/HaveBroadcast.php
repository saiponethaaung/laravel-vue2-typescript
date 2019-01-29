<?php

namespace App\Traits\Eloquent;

trait HaveBroadcast {
    public function broadcast()
    {
        return $this->hasOne('App\Models\Broadcast', 'id', 'project_broadcast_id');
    }
}