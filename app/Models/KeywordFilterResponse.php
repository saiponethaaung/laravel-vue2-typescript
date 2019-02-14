<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordFilterResponse extends Model
{
    protected $table = 'keywords_filters_response';

    protected $fillable = [
        'type',
        'reply_text',
        'chat_block_section_id',
        'created_by',
        'updated_by',
        'keywords_filters_group_rule_id'
    ];

    public function section()
    {
        return $this->hasOne('App\Models\ChatBlockSection', 'id', 'chat_block_section_id');
    }
}
