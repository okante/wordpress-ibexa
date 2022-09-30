<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\MediaNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Media;

final class MediaService extends AbstractService
{
    public const ROOT        = 'media';
    public const SERVICE_URL = '/media';
    public const DATATYPE    = 'media';

    protected string $objectClass    = Media::class;
    protected string $exceptionClass = MediaNotFoundException::class;
}
