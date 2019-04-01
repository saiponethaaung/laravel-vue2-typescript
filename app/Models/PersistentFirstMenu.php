<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentFirstMenu extends Model
{
    protected $table = 'project_persistance_menu';

    protected $fillable = [
        'title',
        'project_id',
        'type',
        'block_id',
        'url',
        'order',
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($firstMenu) {
            foreach($firstMenu->secondRelation as $second) {
                $second->delete();
            }
        });
    }

    public function secondRelation()
    {
        return $this->hasMany('App\Models\PersistentSecondMenu', 'parent_id', 'id');
    }

    public function blocks()
    {
        return $this->hasMany('App\Models\ChatBlockSection', 'id', 'block_id');
    }
}
