<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordFilterGroupRule extends Model
{
    protected $table = 'keywords_filters_group_rule';

    protected $fillable = [
        'keywords_filters_group_id',
        'created_by',
        'updated_by'
    ];

    public function group()
    {
        return $this->hasOne('App\Models\KeywordGroup', 'id', 'keywords_filters_group_id');
    }

    public function filters()
    {
        return $this->hasMany('App\Models\KeywordFilter', 'keywords_filters_group_rule_id', 'id');
    }

    public function response()
    {
        return $this->hasMany('App\Models\KeywordFilterResponse', 'keywords_filters_group_rule_id', 'id');
    }
}
