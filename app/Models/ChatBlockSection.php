<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlockSection extends Model
{
    protected $table = 'chat_block_section';

    protected $fillable = [
        'block_id',
        'broadcast_id',
        'title',
        'order',
        'type'
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($section) {
            foreach($section->contents as $content) {
                $content->delete();
            }

            foreach($section->buttonBlocks as $btn) {
                $btn->section_id = null;
                $btn->save();
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

    public function buttonBlocks()
    {
        return $this->hasMany('App\Models\ChatButtonBlock', 'section_id', 'id');
    }
}
