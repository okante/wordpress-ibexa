<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Entity;

use Almaviacx\Bundle\Ibexa\WordPress\Service\AuthorService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\CategoryService;
use Almaviacx\Bundle\Ibexa\WordPress\Service\PostService;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class WPData.
 *
 * @ORM\Table(name="wordpress_data")
 * @ORM\Entity(repositoryClass="Almaviacx\Bundle\Ibexa\WordPress\Repository\WPData")
 */
class WPData
{
    public const DATATYPE_POST     = PostService::DATATYPE;
    public const DATATYPE_PAGE     = PostService::DATATYPE;
    public const DATATYPE_CATEGORY = CategoryService::DATATYPE;
    public const DATATYPE_TAG      = CategoryService::DATATYPE;
    public const DATATYPE_AUTHOR   = AuthorService::DATATYPE;

    /**
     * @ORM\Id
     * @ORM\Column(name="`data_id`", type="integer", nullable=false))
     */
    protected int $dataId;

    /**
     * @ORM\Id
     * @ORM\Column(name="`data_type`", type="string", length=32, nullable=false)
     */
    protected string $dataType;

    /**
     * @ORM\Column(name="`data_content`", type="text")
     */
    protected string $dataContent;

    public function getDataId(): int
    {
        return $this->dataId;
    }

    public function setDataId(int $dataId): void
    {
        $this->dataId = $dataId;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }

    public function getDataContent(): string
    {
        return $this->dataContent;
    }

    public function setDataContent(string $dataContent): void
    {
        $this->dataContent = $dataContent;
    }
}
