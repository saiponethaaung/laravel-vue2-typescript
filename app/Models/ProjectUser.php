<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $table = 'project_user';

    /**
     * 
     * User Type
     * 
     * 0. Owner
     * 1. Member
     * 2. View only
     * 
     */

    protected $fillable = [
        'project_id',
        'user_id',
        'user_type'
    ];

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}
