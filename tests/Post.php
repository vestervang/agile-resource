<?php

namespace Vestervang\AgileResource\Test;

use Vestervang\AgileResource\Models\BaseModel;

class Post extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'body'
    ];

    protected $mapping = [
        [
            'backend' => 'title',
            'frontend' => 'Title',
        ],
        [
            'backend' => 'body',
            'frontend' => 'Body',
        ],
        [
            'backend' => 'author',
            'frontend' => 'Author',
        ],
    ];


    public function author()
    {
        return $this->hasOne(User::class, 'id', 'author_id');
    }
}
