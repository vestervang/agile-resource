<?php


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

Builder::macro('paginator', function ($columns = ['*'], $pageName = 'page', $page = null) {
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