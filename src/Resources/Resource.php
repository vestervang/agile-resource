<?php

namespace Vestervang\AgileResource\Resources;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use stdClass;
use Vestervang\AgileResource\Models\BaseModel;
use Vestervang\AgileResource\Traits\Filterable;

class Resource extends JsonResource
{
    private $fields;

    public function __construct($resource, $fields = null)
    {
        parent::__construct($resource);
        $this->fields = $fields;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        if (is_scalar($this->resource)) {
            return [
                $this->resource
            ];
        }

        $result = [];

        $this->fields = $this->getFields($request);

        if ($this->resource instanceof stdClass) {
            $this->resource = (array)$this->resource;
        }

        if (is_array($this->resource)) {
            return $this->makeArrayResponse($request);
        }

        // Laravel will handle the converstion of the carbon class (doesn't support the field filtering at the moment)
        if ($this->resource instanceof Carbon) {
            return $this->resource;
        }

        if ($this->resource instanceof Collection) {
            return (new ResourceCollection($this->resource, $this->fields))->toArray($request);
        }

        if ($this->resource instanceof LengthAwarePaginator) {
            return (new ResourcePaginationCollection($this->resource, $this->fields))->toArray($request);
        }

        $traits = class_uses_recursive(get_class($this->resource));

        if (!in_array(Filterable::class, $traits)) {
            throw new Exception('Model has to extend ' . BaseModel::class . ' to be supported!');
        }

        $mapping = $this->resource->getMapping();

        if (empty($mapping)) {
            throw new Exception('No mapping found');
        }

        if (
            $this->fields == null ||
            $this->fields == [] ||
            (is_array($this->fields) && isset($this->fields[0]) && $this->fields[0] == '')
        ) {
            $this->fields = array_column($mapping, 'frontend');
        }

        foreach ($this->fields as $fieldKey => $field) {
            if (is_array($field)) {
                $resultKey = $fieldKey;
            } else {
                $resultKey = $field;
            }


            $key = array_search($resultKey, array_column($mapping, 'frontend'));

            if ($key === false) {
                continue;
            }

            $currentMap = $mapping[$key];

            $isRelationship = $this->resource->isRelationship($currentMap['backend']);

            if ($isRelationship) {
                $item = $this->handleRelationship($field, $currentMap);
            } else {
                $item = $this->{$currentMap['backend']};
            }

            $result[$resultKey] = $item;
        }
        return $result;
    }

    protected function makeRequestArray($fields): ?array
    {
        if ($fields == null || $fields == '') {
            return null;
        }

        $fields = $this->parseFields($fields);

        $result = [];

        foreach ($fields as $field) {
            $pos = strpos($field, "[");

            if ($pos === false) {
                $result[] = $field;
                continue;
            }

            $key = substr($field, 0, $pos);
            $value = substr($field, $pos + 1, -1);

            $selectedItems = $this->parseFields($value);

            foreach ($selectedItems as $selectedItem) {
                $nested = strpos($selectedItem, '[') !== false;

                if ($nested) {
                    $tmpArray = $this->makeRequestArray($selectedItem);
                    $result[$key][key($tmpArray)] = current($tmpArray);
                } else {
                    $result[$key][] = $selectedItem;
                }
            }
        }
        return $result;
    }

    protected function parseFields($fields): array
    {
        $result = [];
        $fieldsLength = mb_strlen($fields);

        $level = 0;
        $splitPositions = [0];

        for ($i = 0; $i < $fieldsLength; $i++) {
            $char = $fields[$i];

            if ($char === '[') {
                $level++;
            }

            if ($char === ']') {
                $level--;
            }

            if ($char === ',' && $level == 0) {
                $splitPositions[] = $i;
            }
        }

        $splitPositions[] = $fieldsLength;

        // We do not want to loop over the last element
        $commaCount = count($splitPositions) - 1;
        for ($i = 0; $i < $commaCount; $i++) {
            $start = $splitPositions[$i];
            $end = $splitPositions[($i + 1)] - $start;
            $result[] = ltrim(mb_substr($fields, $start, $end), ',');
        }
        return $result;
    }

    protected function getFields($request): ?array
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        $fields = $this->fields;

        if ($request instanceof Request) {
            $fields = $this->makeRequestArray(str_replace(' ', '', $request->get('fields')));
        }

        if (is_string($this->fields)) {
            $fields = $this->makeRequestArray(str_replace(' ', '', $this->fields));
        }

        if(is_array($request)){
            $fields = $request;
        }

        return $fields;
    }

    protected function makeArrayResponse($request) : array
    {
        $result = [];

        if ($this->fields === null) {
            $this->fields = array_keys($this->resource);
        }

        foreach ($this->fields as $fieldKey => $fieldValue) {
            if (is_array($fieldValue)) {
                $key = $fieldKey;
            } else {
                $key = $fieldValue;
            }

            if (!isset($this->resource[$key])) {
                continue;
            }

            $item = $this->resource[$key];

            if (!is_scalar($item)) {
                $item = (new Resource($item, $this->fields[$key] ?? null))->toArray($request);
            }

            $result[$key] = $item;
        }

        return $result;
    }

    protected function buildRelationshipUrl($currentMap): ?string
    {
        $route = $currentMap['routeName'] ?? null;
        if (!Route::has($route)) {
            return null;
        }

        $url = route($currentMap['routeName'], $this->resource->toArray());
        return strtok($url, "?");
    }

    protected function handleRelationship($field, $map)
    {
        $isLoaded = $this->resource->relationLoaded($map['backend']);

        if ($isLoaded) {
            $item = (new Resource($this->resource->{$map['backend']}))->toArray($field);
        } else {
            $item = $this->buildRelationshipUrl($map);
        }

        return $item;
    }
}
