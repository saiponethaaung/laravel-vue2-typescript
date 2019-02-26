<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectInvite extends Model
{
    protected $table = 'project_invite';

    protected $fillable = [
        'email',
        'user_id',
        'role',
        'project_id',
        'status'
    ];
}
