<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentSecondMenu extends Model
{
    protected $table = 'project_persistance_second_menu';

    protected $fillable = [
        'title',
        'parent_id',
        'type',
        'block_id',
        'url',
        'order',
    ];

    public static function boot() {
        parent::boot();

        static::deleting(function($secondMenu) {
            foreach($secondMenu->thirdRelation as $third) {
                $third->delete();
            }
        });
    }

    public function thirdRelation()
    {
        return $this->hasMany('App\Models\PersistentThirdMenu', 'parent_id', 'id');
    }

    public function blocks()
    {
        return $this->hasMany('App\Models\ChatBlockSection', 'id', 'block_id');
    }
}
