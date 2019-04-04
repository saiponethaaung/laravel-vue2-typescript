<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPage extends Model
{
    protected $table = 'project_page';

    protected $fillable = [
        'project_id',
        'page_id',
        'page_icon',
        'publish',
        'token'
    ];

    public function projectRelation()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}
