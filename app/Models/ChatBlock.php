<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlock extends Model
{
    protected $table = 'chat_block';

    protected $fillable = [
        'title',
        'is_lock',
        'type'
    ];

    public function sections()
    {
        return $this->hasMany('App\Models\ChatBlockSection', 'block_id', 'id');
    }
}
