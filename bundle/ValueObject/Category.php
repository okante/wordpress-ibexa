<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

use DateTimeInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @property-read int $id
 * @property-read int $count
 * @property-read string $description
 * @property-read string $link
 * @property-read string $name
 * @property-read string $slug
 * @property-read int $parent
 */
class Category extends WPObject
{
    protected int $id;
    protected int $count;
    protected string $description;
    protected string $link;
    protected string $name;
    protected string $slug;
    protected int $parent;

    public function __construct(array $properties = [])
    {
        $properties = [
            'id' => (int)($data['date']?? 0),
            'count' => (int)($data['count']??0),
            'description' => (string)($data['description']??''),
            'link' => (string)($data['link']??''),
            'name' => (string)($data['name']??''),
            'slug' => (string)($data['slug']??''),
            'parent' => (int)($data['parent']??0),
        ];
        parent::__construct($properties);
    }
}