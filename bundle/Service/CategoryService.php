<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\CategoryNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\PostNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Category;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\Post;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;

final class CategoryService extends AbstractService
{
    public const ROOT = 'categories';
    public const SERVICE_URL = '/categories';

    protected string $objectClass = Category::class;
    protected string $exceptionClass = CategoryNotFoundException::class;
}