<?php

namespace App\Models;

use Storage;

use Illuminate\Database\Eloquent\Model;

class ChatBlockSectionContent extends Model
{
    protected $table = 'chat_block_section_content';

    protected $fillable = [
        'section_id',
        'order',
        'type',
        'text',
        'content',
        'image',
        'duration'
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($content) {
            if(is_null($content->image)===false && $content->image!=='' && Storage::disk('public')->exists('/images/photos/'.$content->image)) {
                Storage::disk('public')->delete('/images/photos/'.$content->image);
            }

            foreach($content->buttons as $button) {
                $button->delete();
            }

            foreach($content->galleryList as $gallery) {
                $gallery->delete();
            }

            foreach($content->quickReply as $qr) {
                $qr->delete();
            }

            foreach($content->userInput as $ui) {
                $ui->delete();
            }
        });
    }

    public function section()
    {
        return $this->hasOne('App\Models\ChatBlockSection', 'id', 'section_id');
    }

    public function galleryList()
    {
        return $this->hasMany('App\Models\ChatGallery', 'content_id', 'id');
    }

    public function buttons()
    {
        return $this->hasMany('App\Models\ChatButton', 'content_id', 'id');
    }

    public function quickReply()
    {
        return $this->hasMany('App\Models\ChatQuickReply', 'content_id', 'id');
    }

    public function userInput()
    {
        return $this->hasMany('App\Models\ChatUserInput', 'content_id', 'id');
    }
}
