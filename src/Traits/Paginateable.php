<?php

namespace Vestervang\AgileResource\Traits;

trait Paginateable
{
    protected $defaultPerPage = 10;
    protected $defaultPerPageLimit = 100;

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