<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttribute extends Model
{
    protected $table = 'chat_attribute';

    protected $fillable = [
        'attribute'
    ];

    public function chatValue() {
        return $this->hasMany('App\Models\ProjectPageUserAttribute', 'attribute_id', 'id');
    }
}
