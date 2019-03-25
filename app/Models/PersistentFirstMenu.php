<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentFirstMenu extends Model
{
    protected $table = 'project_persistent_menu';

    protected $fillable = [
        'title',
        'project_id',
        'type',
        'block_id',
        'url',
        'order',
    ];

    public function secondRelation()
    {
        return $this->hasMany('App\Models\PersistentSecondMenu', 'parent_id', 'id');
    }
}
