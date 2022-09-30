<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Almaviacx\Bundle\Ibexa\WordPress\Service\CategoryService;

trait CategoryServiceAware
{
    protected CategoryService $categoryService;

    /**
     * @required
     */
    public function setCategoryService(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
}
