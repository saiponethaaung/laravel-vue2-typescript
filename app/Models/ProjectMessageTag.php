<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMessageTag extends Model
{
    protected $table = 'project_message_tag';

    protected $fillable = [
        'name',
        'tag_format',
        'mesg',
        'notice',
        'is_primary'
    ];
}
