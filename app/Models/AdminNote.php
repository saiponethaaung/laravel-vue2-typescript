<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNote extends Model
{
    protected $table = 'project_page_user_note';

    protected $fillable = [
        'project_page_user_id',
        'project_user_id',
        'note'
    ];

    public function projectUser()
    {
        return $this->hasOne('App\Models\ProjectUser', 'id', 'project_user_id');
    }
}
