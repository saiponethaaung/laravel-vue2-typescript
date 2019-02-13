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
}
