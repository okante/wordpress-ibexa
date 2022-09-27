<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Almaviacx\Bundle\Ibexa\WordPress\Service\AuthorService;

trait AuthorServiceAware
{

    protected AuthorService $authorService;

    /**
     * @required
     */
    public function setAuthorService(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }
}