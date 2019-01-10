<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPageUser extends Model
{
    protected $table = 'project_page_user';

    protected $fillable = [
        'project_page_id',
        'fb_user_id',
        'live_chat',
        'user_id',
        'urgent'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\ProjectPageUserAttribute', 'project_page_user_id', 'id');
    }
}
