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

    public function projectPage()
    {
        return $this->hasOne('App\Models\ProjectPage', 'id', 'project_page_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\ProjectPageUserAttribute', 'project_page_user_id', 'id');
    }

    public function chat()
    {
        return $this->hasOne('App\Models\ProjectPageUserChat', 'project_page_user_id', 'id');
    }

    public function fav()
    {
        return $this->hasOne('App\Models\ProjectPageUserFav', 'project_page_user_id', 'id');
    }
}
