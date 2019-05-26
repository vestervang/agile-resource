<?php

namespace Vestervang\AgileResource\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $mapping = [];

    /**
     * @return array;
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }

    public function isRelationship($attr)
    {
        return !$this->hasAttribute($attr);
    }
}