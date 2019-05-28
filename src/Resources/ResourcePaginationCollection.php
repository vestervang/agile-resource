<?php

namespace Vestervang\AgileResource\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Vestervang\AgileResource\Resources\ResourceCollection as AgileResourceCollection;

class ResourcePaginationCollection extends ResourceCollection
{
    protected $metaData;
    protected $links;
    protected $fields;

    /**
     * ResourcePaginationCollection constructor.
     * @param LengthAwarePaginator $resource
     * @param array $fields
     */
    public function __construct($resource, $fields = null)
    {
        $this->links = [
            'next_page' => $resource->nextPageUrl(),
            'previous_page' => $resource->previousPageUrl(),
        ];

        $this->metaData = [
            'current_page' => $resource->currentPage(),
            'count' => $resource->count(),
            'per_page' => $resource->perPage(),
            'total' => $resource->total(),
            'total_pages' => $resource->lastPage(),
        ];

        $this->fields = $fields;

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => (new AgileResourceCollection($this->collection, $this->fields))->toArray($request),
            'meta' => $this->metaData,
            'links' => $this->links,
        ];
    }
}