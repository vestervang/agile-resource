<?php

namespace Vestervang\AgileResource\Models;

use Illuminate\Database\Eloquent\Model;
use Vestervang\AgileResource\Traits\Encryptable;
use Vestervang\AgileResource\Traits\Paginateable;

class BaseModel extends Model
{
    use Paginateable, Encryptable;

    protected $mapping = [];
    protected $defaultOrderColumn = 'name';
    protected $defaultOrderDirection = 'desc';
    protected $sortable = ['id'];

    /**
     * @return array;
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    public function getSortableColumns()
    {
        return $this->sortable;
    }

    public function getOrderColumn()
    {
        return $this->defaultOrderColumn;
    }

    public function getOrderDirection()
    {
        return strtolower($this->defaultOrderDirection);
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