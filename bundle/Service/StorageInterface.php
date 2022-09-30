<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;

interface StorageInterface
{
    public function store(string $dataId, string $dataType, WPObject $object): bool;

    public function load(string $dataId, string $dataType): ?WPObject;

    public function clearAll(): void;
}
