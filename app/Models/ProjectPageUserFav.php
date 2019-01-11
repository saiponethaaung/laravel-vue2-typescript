<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPageUserFav extends Model
{
    protected $table = 'user_fav_project_page_user';

    protected $fillable = [
        'project_page_user_id',
        'project_user_id',
        'status'
    ];
}
