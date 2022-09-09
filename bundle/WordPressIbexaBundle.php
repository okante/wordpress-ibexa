<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress;

use Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection\WordPressIbexaExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WordPressIbexaBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new WordPressIbexaExtension();
        }

        return $this->extension;
    }
}
