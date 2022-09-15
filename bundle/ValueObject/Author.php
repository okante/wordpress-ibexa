<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

/**
 * @property-read string $name
 * @property-read string $url
 * @property-read string $description
 * @property-read string $link
 * @property-read string $slug
 * @property-read array $avatar_urls
 * @property-read array $metas
 */
class Author extends WPObject
{
    protected string $name;
    protected string $url;
    protected string $description;
    protected string $link;
    protected string $slug;
    protected array $avatar_urls;
    protected array $metas;

    public function __construct(array $data = [])
    {
        $properties = [
            'id' => $data['id']?? 0,
            'name' => (string)($data['name']??''),
            'url' => (string)($data['url']??''),
            'description' => (string)($data['description']??''),
            'link' => (string)($data['link']??''),
            'slug' => (string)($data['slug']??''),
            'avatar_urls' => (array)($data['avatar_urls']??[]),
            'metas' => (array)($data['metas']??[]),
        ];
        parent::__construct($properties);
    }

    public function getWPObjectTitle(): ?string
    {
        return $this->name;
    }

    function getWPObjectId(): ?int
    {
        return $this->id;
    }
}