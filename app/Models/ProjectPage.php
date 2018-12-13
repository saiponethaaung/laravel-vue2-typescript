<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPage extends Model
{
    protected $table = 'project_page';

    protected $fillable = [
        'project_id',
        'page_id',
        'token'
    ];
}
