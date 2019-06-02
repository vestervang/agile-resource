<?php

namespace Vestervang\AgileResource\Test;

use Vestervang\AgileResource\Models\BaseModel;

class User extends BaseModel
{
    protected $mapping = [
        [
            'backend' => 'name',
            'frontend' => 'Name',
        ],
        [
            'backend' => 'email',
            'frontend' => 'Email',
        ],
        [
            'backend' => 'posts',
            'frontend' => 'Posts',
            'routeName' => 'user.posts',
        ]
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}
