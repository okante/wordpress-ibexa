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
     * @var int
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
     * @var string
     * @ORM\Column(name="`data_content`", type="text")
     */
    protected string $dataContent;

    /**
     * @return int
     */
    public function getDataId(): int
    {
        return $this->dataId;
    }

    /**
     * @param int $dataId
     */
    public function setDataId(int $dataId): void
    {
        $this->dataId = $dataId;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getDataContent(): string
    {
        return $this->dataContent;
    }

    /**
     * @param string $dataContent
     */
    public function setDataContent(string $dataContent): void
    {
        $this->dataContent = $dataContent;
    }

}
