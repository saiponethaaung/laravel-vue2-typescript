<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordFilterGroup extends Model
{
    protected $table = 'keywords_filters_group';

    protected $fillable = [
        'name',
        'project_id',
        'created_by',
        'updated_by'
    ];

    public static function boot() {
        parent::boot();

        static::created(function($kfg) {
            KeywordFilterGroupRule::create([
                'value' => '',
                'keywords_filters_group_id' => $kfg->id,
                'created_by' => $kfg->created_by,
                'updated_by' => $kfg->updated_by
            ]);
        });
    }

    public function rules()
    {
        return $this->hasMany('App\Models\KeywordFilterGroupRule', 'keywords_fiters_group_id', 'id');
    }
}
