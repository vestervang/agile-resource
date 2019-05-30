<?php

namespace Vestervang\AgileResource\Models;

use Illuminate\Database\Eloquent\Model;
use Vestervang\AgileResource\Traits\Encryptable;
use Vestervang\AgileResource\Traits\Paginateable;

class BaseModel extends Model
{
    use Paginateable, Encryptable;

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