<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

interface ServiceInterface
{
    public function import(?int $perPage = null, ?int $page = null): int;
}
