<?php

namespace Vestervang\AgileResource\Traits;

trait Paginateable
{
    /**
     * Override the default getPerPage function to get the perPage variable from the get parameters
     *
     * @return int
     */
    public function getPerPage()
    {
        $perPage = request('perPage', $this->defaultPerPage);
        $perPage = min($perPage, $this->defaultPerPageLimit);
        $this->setPerPage($perPage);
        return $perPage;
    }
}