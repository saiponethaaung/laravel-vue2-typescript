<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPageUserChat extends Model
{
    protected $table = 'project_page_user_chat';

    protected $fillable = [
        'content_id',
        'post_back',
        'from_platform',
        'mesg',
        'mesg_id',
        'is_send',
        'quick_reply_id',
        'user_input_id',
        'input_complete',
        'project_page_user_id',
        'ignore',
        'content_id',
        'is_live',
        'content_type'
    ];
}
