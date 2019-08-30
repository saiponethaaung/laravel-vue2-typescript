<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';

    protected $fillable = [
        'name',
        'timezone',
        'user_id'
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($project) {
            $user = ProjectUser::create([
                'project_id' => $project->id,
                'user_id' => $project->user_id,
                'user_type' => 0
            ]);

            $block = ChatBlock::create([
                'title' => 'Landing',
                'is_lock' => true,
                'project_id' => $project->id,
                'type' => 1
            ]);

            ChatBlockSection::create([
                'block_id' => $block->id,
                'broadcast_id' => null,
                'title' => 'Welcome',
                'type' => 1,
                'order' => 1
            ]);

            ChatBlockSection::create([
                'block_id' => $block->id,
                'broadcast_id' => null,
                'title' => 'Default Answer',
                'type' => 2,
                'order' => 2
            ]);

            KeywordFilterGroup::create([
                'name' => 'Default group',
                'project_id' => $project->id,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);
        });

        static::deleting(function ($project) {
            $project->page->delete();
            foreach ($project->users as $user) {
                $user->delete();
            }
            foreach ($project->chatAttributes as $chatAttribute) {
                $chatAttribute->delete();
            }
        });
    }

    public function user()
    {
        return $this->hasOne('App\Models\ProjectUser', 'project_id', 'id');
    }

    public function users()
    {
        return $this->hasMany('App\Models\ProjectUser', 'project_id', 'id');
    }

    public function chatAttributes()
    {
        return $this->hasMany('App\Models\ChatAttribute', 'project_id', 'id');
    }

    public function page()
    {
        return $this->hasOne('App\Models\ProjectPage', 'project_id', 'id');
    }
}
