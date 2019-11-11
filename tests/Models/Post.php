<?php

namespace Vestervang\AgileResource\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Vestervang\AgileResource\Traits\Filterable;

class Post extends Model
{
    use Filterable;

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
