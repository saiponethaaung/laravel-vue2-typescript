<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $table = 'project_user_segments';

    protected $fillable = [
        'name',
        'project_user_id',
        'project_id'
    ];

    public function filters()
    {
        return $this->hasMany('App\Models\SegmentFilter', 'project_user_segments_id', 'id');
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}
