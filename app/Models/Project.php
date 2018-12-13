<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';

    protected $fillable = [
        'name',
        'timezone',
        'user_id'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\ProjectUser', 'project_id', 'id');
    }

    public function users()
    {
        return $this->hasMany('App\Models\ProjectUser', 'project_id', 'id');
    }

    public function page()
    {
        return $this->hasOne('App\Models\ProjectPage', 'project_id', 'id');
    }
}
