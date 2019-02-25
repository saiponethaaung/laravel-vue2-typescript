<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordFilter extends Model
{
    protected $table = 'keywords_filters';

    protected $fillable = [
        'value',
        'keywords_filters_group_rule_id',
        'created_by',
        'updated_by'
    ];

    public function rule()
    {
        return $this->hasOne('App\Models\KeywordFilterGroupRule', 'id', 'keywords_filters_group_rule_id');
    }
}
