<?php

namespace Vestervang\AgileResource\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Vestervang\AgileResource\Models\BaseModel;

class Resource extends JsonResource
{
    private $fields;
    private $excludes;
    private $excludeActive;

    public function __construct($resource, $fields = null)
    {
        parent::__construct($resource);

        $this->fields = $fields;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     * @throws \Exception
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
        $this->excludes = $this->getExcludedFields($request);

        if ($this->fields == null && $this->excludes !== null) {
            $this->excludeActive = true;
        }

        if (is_array($this->resource) || $this->resource instanceof \stdClass) {
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

        if (!$this->resource instanceof BaseModel) {
            throw new \Exception('Model has to extend ' . BaseModel::class . ' to be supported!');
        }

        $mapping = $this->resource->getMapping();

        if (!$mapping) {
            throw new Exception('No mapping found');
        }

        $i = 0;
        // Check if there is any fields set
        if (
            $this->fields == null ||
            $this->fields == [] ||
            (is_array($this->fields) && isset($this->fields[0]) && $this->fields[0] == '')
        ) {
            $this->fields = array_column($mapping, 'frontend');
        }

        if ($this->excludeActive) {
            $this->deleteExcluedeFields();
        }

        foreach ($this->fields as $fieldKey => $field) {
            if ($this->includeRelationship($field)) {
                $resultKey = $fieldKey;
            } else {
                $resultKey = $field;
            }

            $key = array_search($resultKey, array_column($mapping, 'frontend'));

            // If no key is found jump to the next field
            if ($key === false) {
                continue;
            }

            $currentMap = $mapping[$key];

            $isRelationship = $this->resource->isRelationship($currentMap['backend']);

            if (!$isRelationship) {
                $item = $this->{$currentMap['backend']};
            } else {
                $item = $this->handleRelationship($field, $currentMap);
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

        // We subtract one because we do not want to loop over the last element
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
        if ($this->fields == null) {
            $fields = $this->makeRequestArray(str_replace(' ', '', $request->get('fields')));
        } else {
            if (is_string($this->fields)) {
                $fields = $this->makeRequestArray(str_replace(' ', '', $this->fields));
            } else {
                $fields = $this->fields;
            }
        }
        return $fields;
    }

    protected function getExcludedFields($request): ?array
    {
        $excludes = $this->makeRequestArray($request->get('exclude'));
        return $excludes;
    }

    protected function makeArrayResponse($request)
    {
        $result = [];

        if ($this->fields === null) {
            return $this->resource;
        }

        foreach ($this->fields as $fieldKey => $fieldValue) {
            if (is_array($fieldValue)) {
                $key = $fieldKey;
            } else {
                $key = $fieldValue;
            }

            if (!isset($this->resource[$key]) || $this->excludeKey($key)) {
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

    protected function includeRelationship($field)
    {
        return is_array($field);
    }

    protected function checkPermission($currentMap): bool
    {
        $hasPermission = true;

        $permissions = (isset($currentMap['permissions']) && $currentMap['permissions'] !== '')
            ? explode('|', $currentMap['permissions'])
            : [];

        foreach ($permissions as $permission) {
            $check = false;
            try {
                $check = auth()->user()->hasPermissionTo($permission);
            } catch (PermissionDoesNotExist $e) {
                // Do nothing
            }
            if ($check) {
                $hasPermission = true;
                break;
            } else {
                $hasPermission = false;
            }
        }

        return $hasPermission;
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

    protected function getRelationship($currentMap, $fields)
    {
        $relationshipData = $this->{$currentMap['backend']};
        $item = null;
        if ($relationshipData != null) {
            $relationshipType = class_basename($relationshipData);

            $relationshipFields = null;
            if (isset($fields[$currentMap['frontend']])) {
                $relationshipFields = $fields[$currentMap['frontend']];
            }

            if ($relationshipType == 'Collection') {
                $item = (new ResourceCollection($relationshipData, $relationshipFields))->toArray(request());
            } else {
                $item = (new Resource($relationshipData, $relationshipFields))->toArray(request());
            }
        }
        return $item;
    }

    protected function excludeKey($key)
    {
        return is_array($this->excludes) ? in_array($key, $this->excludes) : false;
    }

    protected function deleteExcluedeFields()
    {
        foreach ($this->excludes as $exclude) {
            $key = array_search($exclude, $this->fields);

            if ($key === false) {
                continue;
            }
            unset($this->fields[$key]);
        }
    }

    protected function handleRelationship($field, $map)
    {
        $includeRelationship = $this->includeRelationship($field);

        if ($includeRelationship) {
            $item = $this->getRelationship($map, $this->fields);
        }

        if (!$includeRelationship) {
            $item = $this->buildRelationshipUrl($map);
        }

        return $item;
    }
}