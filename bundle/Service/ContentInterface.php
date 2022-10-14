<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

interface ContentInterface
{
    /**
     * @throws ContentFieldValidationException
     * @throws InvalidArgumentException
     * @throws BadStateException
     * @throws ContentValidationException
     * @throws UnauthorizedException
     * @throws NotFoundException
     */
    public function createContent(
        WPObject $object,
        array $values,
        string $remoteId,
        int $parentLocationId = null,
        bool $update = false
    ): ?Content;
}
