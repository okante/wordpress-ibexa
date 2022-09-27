<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

/**
 * @property int    $id
 * @property string $source_url
 */
class Media extends WPObject
{
    protected static array $renderedAttributes = ['guid', 'title'];
    protected int $id;
    protected string $guid;
    protected string $title;
    protected string $source_url;
    protected string $link;
    protected string $slug;

    public function __construct(array $data = [])
    {
        $properties = [
            'id' => (int) ($data['id'] ?? 0),
            'guid' => $data['guid'] ?? '',
            'link' => $data['link'] ?? '',
            'title' => ($data['title'] ?? ''),
            'slug' => (string) ($data['slug'] ?? ''),
            'source_url' => (string) ($data['source_url'] ?? ''),
        ];
        parent::__construct($properties);
    }

    public function getWPObjectTitle(): ?string
    {
        return $this->title;
    }

    public function getWPObjectId(): ?int
    {
        return $this->id;
    }
}
