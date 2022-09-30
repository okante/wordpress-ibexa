<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Storage;

use Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection\Configuration;
use Almaviacx\Bundle\Ibexa\WordPress\Entity\WPData;
use Almaviacx\Bundle\Ibexa\WordPress\Service\StorageInterface;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\ConfigResolverTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\LoggerTrait;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class Doctrine implements StorageInterface
{
    use ConfigResolverTrait;
    use LoggerTrait;

    protected const NAMESPACE = Configuration::NAMESPACE;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function store(string $dataId, string $dataType, WPObject $object): bool
    {
        /*
        $wpData = new WPData();
        $wpData->setDataId((int)$dataId);
        $wpData->setDataType($dataType);
        $wpData->setDataContent(json_encode($object->toArray()));
        $this->entityManager->persist($wpData);
        $this->entityManager->flush();
         */
        throw new RuntimeException(__METHOD__.' is not implemented');
    }

    public function load(string $dataId, string $dataType): ?WPObject
    {
        throw new RuntimeException(__METHOD__.' is not implemented');
    }

    public function clearAll(): void
    {
        throw new RuntimeException(__METHOD__.' is not implemented');
    }
}
