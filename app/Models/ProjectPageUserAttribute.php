<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPageUserAttribute extends Model
{
    protected $table = 'project_page_user_attribute';

    protected $fillable = [
        'attribute_id',
        'value',
        'project_page_user_id'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\ProjectPageUser', 'id', 'project_page_user_id');
    }

    public function attrValue()
    {
        return $this->hasOne('App\Models\ChatAttribute', 'id', 'attribute_id');
    }
}
