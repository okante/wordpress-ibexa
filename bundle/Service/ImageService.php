<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\ImageNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Image;

final class ImageService extends AbstractService
{
    public const ROOT        = 'image';
    public const SERVICE_URL = '/media';
    public const DATATYPE    = 'image';

    protected string $objectClass    = Image::class;
    protected string $exceptionClass = ImageNotFoundException::class;
}
