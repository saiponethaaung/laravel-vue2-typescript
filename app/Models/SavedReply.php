<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedReply extends Model
{
    protected $table = 'project_page_user_saved_reply';

    protected $fillable = [
        'title',
        'message',
        'project_id',
        'created_by',
        'updated_by'
    ];

    public function projectUser()
    {
        return $this->hasOne('App\Models\ProjectUser', 'id', 'created_by');
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}
