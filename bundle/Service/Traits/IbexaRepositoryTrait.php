<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Ibexa\Contracts\Core\Repository\Repository;

trait IbexaRepositoryTrait
{
    protected Repository $repository;

    /**
     * @required
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }
}