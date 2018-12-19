<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlock extends Model
{
    protected $table = 'chat_block';

    protected $fillable = [
        'title',
        'is_lock',
        'project_id',
        'type'
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($block) {
            foreach($block->sections as $section) {
                $section->delete();
            }
        });
    }

    public function sections()
    {
        return $this->hasMany('App\Models\ChatBlockSection', 'block_id', 'id');
    }
}
