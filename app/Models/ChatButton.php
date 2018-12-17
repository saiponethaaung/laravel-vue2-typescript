<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatButton extends Model
{
    protected $table = 'chat_button';

    protected $fillable = [
        'title',
        'text',
        'phone',
        'url',
        'content_id',
        'gallery_id',
        'action_type',
        'order'
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($button) {
            foreach($button->blocks as $block) {
                $block->delete();
            }
        });
    }

    public function blocks()
    {
        return $this->hasMany('App\Models\ChatButtonBlock', 'button_id', 'id');
    }
}
