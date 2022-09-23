<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

/**
 * @property int    $count
 * @property string $description
 * @property string $link
 * @property string $name
 * @property string $slug
 * @property int    $parent
 */
class Category extends WPObject
{
    protected int $count;
    protected string $description;
    protected string $link;
    protected string $name;
    protected string $slug;
    protected int $parent;

    public function __construct(array $data = [])
    {
        $properties = [
            'id' => (int) ($data['id'] ?? 0),
            'count' => (int) ($data['count'] ?? 0),
            'description' => (string) ($data['description'] ?? ''),
            'link' => (string) ($data['link'] ?? ''),
            'name' => (string) ($data['name'] ?? ''),
            'slug' => (string) ($data['slug'] ?? ''),
            'parent' => (int) ($data['parent'] ?? 0),
        ];
        parent::__construct($properties);
    }

    public function getWPObjectTitle(): ?string
    {
        return $this->name;
    }

    public function getWPObjectId(): ?int
    {
        return $this->id;
    }
}
