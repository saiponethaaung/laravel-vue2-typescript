<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectInviteEmail extends Model
{
    protected $table = 'project_invite_email';

    protected $fillable = [
        'status',
        'project_invite_id',
        'project_user_id'
    ];
}
