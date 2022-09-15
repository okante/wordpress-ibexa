<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

use DateTimeInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

/**
 * @property-read DateTimeInterface $date
 * @property-read DateTimeInterface $date_gmt
 * @property-read string $guid
 * @property-read DateTimeInterface $modified
 * @property-read DateTimeInterface $modified_gmt
 * @property-read string $slug
 * @property-read string $status
 * @property-read string $type
 * @property-read string $link
 * @property-read string $title
 * @property-read string $content
 * @property-read string $featured_media
 * @property-read string $comment_status
 * @property-read string $ping_status
 * @property-read string $template
 * @property-read string $format
 * @property-read array $metas
 * @property-read Category[] $categories
 * @property-read array $categoryIds
 * @property-read array $tags
 * @property-read ?int $authorId
 * @property-read Author $author
 * @property-read ContentInfo $authorContentInfo
 */
class Post extends WPObject
{
    protected static array $renderedAttributes = ['guid', 'title', 'content'];
    protected static array $dateAttributes = ['date', 'date_gmt', 'modified', 'modified_gmt'];
    protected DateTimeInterface $date;
    protected DateTimeInterface $date_gmt;
    protected string $guid;
    protected DateTimeInterface $modified;
    protected DateTimeInterface $modified_gmt;
    protected string $slug;
    protected string $status;
    protected string $type;
    protected string $link;
    protected string $title;
    protected string $content;
    protected string $featured_media;
    protected string $comment_status;
    protected string $ping_status;
    protected bool $sticky;
    protected string $template;
    protected string $format;
    protected array $metas = [];
    protected array $categories = [];
    protected array $categoryIds = [];
    protected array $tags = [];
    protected ?int $authorId;
    protected ?Author $author;
    protected ?ContentInfo $authorContentInfo;

    public function __construct(array $data = [])
    {
        $properties = [
            'id' => (int)($data['id']?? 0),
            'date' => $data['date']??null,
            'date_gmt' => $data['date_gmt']??null,
            'guid' => ($data['guid']??''),
            'modified' => $data['modified']??null,
            'modified_gmt' => $data['modified_gmt']??null,
            'slug' => (string)($data['slug']??''),
            'status' => (string)($data['status']??''),
            'type' => (string)($data['type']??''),
            'link' => (string)($data['link']??''),
            'title' => ($data['title']??''),
            'content' => ($data['content']['rendered']??''),
            'featured_media' => (int)($data['featured_media']??0),
            'comment_status' => (string)($data['comment_status']??''),
            'ping_status' => (string)($data['ping_status']??''),
            'sticky' => (bool)($data['sticky']??false),
            'template' => (string)($data['template']??''),
            'format' => (string)($data['format']??''),
            'metas' => (array)($data['metas']??[]),
            'categories' => (array)($data['categories']??[]),
            'categoryIds' => (array)($data['categoryIds']??[]),
            'tags' => (array)($data['tags']??[]),
            'authorId' => (int)($data['authorId']??null),
            'author' => ($data['author']??null),
            'authorContentInfo' => ($data['authorContentInfo']??null),
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

    public function setAuthorContent(ContentInfo $authorContentInfo)
    {
        $this->authorContentInfo = $authorContentInfo;
    }
}