<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentThirdMenu extends Model
{
    protected $table = 'project_persistance_third_menu';

    protected $fillable = [
        'title',
        'parent_id',
        'type',
        'block_id',
        'url',
        'order',
    ];

    public function blocks()
    {
        return $this->hasMany('App\Models\ChatBlockSection', 'id', 'block_id');
    }
}
