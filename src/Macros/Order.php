<?php

use Illuminate\Database\Eloquent\Builder;
use Vestervang\AgileResource\Enums\HttpCode;
use Vestervang\AgileResource\Models\BaseModel;

Builder::macro('order', function () {

    if(!$this->model instanceof BaseModel) {
        throw new Exception('Model should of type: ' . BaseModel::class);
    }

    $orderDirectionsPossible = ['asc', 'desc'];

    $orderBys = explode(',', request('orderBy'));
    $orderDirs = explode(',', request('orderDir'));

    for ($i = 0; $i < count($orderBys); $i++) {
        $orderBy = (isset($orderBys[$i]) && $orderBys[$i] !== '')
            ? $orderBys[$i] : $this->model->getOrderColumn();

        $orderDir = (isset($orderDirs[$i]) && $orderDirs[$i] !== '')
            ? strtolower($orderDirs[$i]) : $this->model->getOrderDirection();

        if ($orderBy === null) {
            return $this;
        }

        $key = array_search($orderBy, array_column($this->model->getMapping(), 'frontend'));

        $modelColumn = $this->model->getMapping()[$key]['backend'];

        if (!in_array($modelColumn, $this->model->getSortableColumns())) {
            throw new HttpException(HttpCode::BAD_REQUEST, 'column ' . $orderBy . ' is not sortable');
        }

        if (!in_array($orderDir, $orderDirectionsPossible)) {
            throw new HttpException(HttpCode::BAD_REQUEST, 'Order direction is invalid');
        }

        $this->orderBy($modelColumn, $orderDir);
    }

    return $this;
});
