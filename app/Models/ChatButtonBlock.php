<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatButtonBlock extends Model
{
    protected $table = 'chat_button_block';

    protected $fillable = [
        'button_id',
        'section_id'
    ];

    public function value()
    {
        return $this->hasOne('App\Models\ChatBlockSection', 'id', 'section_id');
    }
}
