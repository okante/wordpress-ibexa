<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use ArrayObject;

interface ServiceInterface
{
    public function import(?int $perPage = null, ?int $page = null, ?array $options = null): ArrayObject;
}
