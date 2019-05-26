<?php

namespace Vestervang\AgileResource\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection AS BaseResourceCollection;

class CollectionResource extends BaseResourceCollection
{
    private $fields;

    public function __construct($resource, $fields = null)
    {
        $this->fields = $fields;
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $result = [];

        foreach($this->resource as $item) {
            $result[] = (new Resource($item, $this->fields))->toArray($request);
        }

        return $result;
    }
}