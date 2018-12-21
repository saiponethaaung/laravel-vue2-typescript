<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPageUser extends Model
{
    protected $table = 'project_page_user';

    protected $fillable = [
        'project_page_id',
        'fb_user_id',
        'live_chat'
    ];
}
