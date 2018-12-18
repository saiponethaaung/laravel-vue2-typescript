<?php

namespace App\Models;

use Storage;

use Illuminate\Database\Eloquent\Model;

class ChatGallery extends Model
{
    protected $table = 'chat_gallery';

    protected $fillable = [
        'title',
        'sub',
        'image',
        'url',
        'type',
        'order',
        'content_id'
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($gallery) {
            $folder = $gallery->type===1 ? 'list' : 'gallery';
            if(is_null($gallery->image)===false && $gallery->image!=='' && Storage::disk('public')->exists('/images/'.$folder.'/'.$gallery->image)) {
                Storage::disk('public')->delete('/images/'.$folder.'/'.$gallery->image);
            }
            foreach($gallery->buttons as $button) {
                $button->delete();
            }
        });
    }

    public function content()
    {
        return $this->hasOne('App\Models\ChatBlockSectionContent', 'id', 'content_id');
    }

    public function buttons()
    {
        return $this->hasMany('App\Models\ChatButton', 'gallery_id', 'id');
    }
}
