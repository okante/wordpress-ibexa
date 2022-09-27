<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Almaviacx\Bundle\Ibexa\WordPress\Service\ImageService;

trait ImageServiceAware
{

    protected ImageService $imageService;

    /**
     * @required
     */
    public function setImageService(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
}