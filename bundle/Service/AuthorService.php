<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\AuthorNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Author;

final class AuthorService extends AbstractService
{
    public const ROOT = 'categories';
    public const SERVICE_URL = '/users';
    public const CACHE_SUFFIX = 'author';


    protected string $objectClass = Author::class;
    protected string $exceptionClass = AuthorNotFoundException::class;
}