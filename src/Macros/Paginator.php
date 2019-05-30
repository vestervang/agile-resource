<?php


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Vestervang\AgileResource\Models\BaseModel;


Builder::macro('paginator', function ($columns = ['*'], $pageName = 'page', $page = null) {

    if(!$this->model instanceof BaseModel) {
        throw new Exception('Model has to be a instance of ' . BaseModel::class);
    }

    $page = $page ?: Paginator::resolveCurrentPage($pageName);

    // Default to 15 if nothing is returned
    $perPage = $this->model->getPerPage() ?: 15;

    $results = ($total = $this->toBase()->getCountForPagination())
        ? $this->forPage($page, $perPage)->order()->get($columns)
        : $this->model->newCollection();

    return $this->paginator($results, $total, $perPage, $page, [
        'path'     => Paginator::resolveCurrentPath(),
        'pageName' => $pageName,
    ])->appends(array_except(request()->input(), 'page'));
});