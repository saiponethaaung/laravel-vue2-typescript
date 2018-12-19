<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlockSection extends Model
{
    protected $table = 'chat_block_section';

    protected $fillable = [
        'block_id',
        'title',
        'order'
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($section) {
            foreach($section->contents as $content) {
                $content->delete();
            }
        });

    }

    public function block()
    {
        return $this->hasOne('App\Models\ChatBlock', 'id', 'block_id');
    }

    public function contents()
    {
        return $this->hasMany('App\Models\ChatBlockSectionContent', 'section_id', 'id');
    }
}
