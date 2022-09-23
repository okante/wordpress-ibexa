<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Storage;

use Almaviacx\Bundle\Ibexa\WordPress\DependencyInjection\Configuration;
use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use Almaviacx\Bundle\Ibexa\WordPress\Service\StorageInterface;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\ConfigResolverTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\LoggerTrait;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Redis implements StorageInterface
{
    use ConfigResolverTrait;
    use LoggerTrait;

    protected const NAMESPACE = Configuration::NAMESPACE;
    private TagAwareAdapterInterface $cachePool;

    public function __construct(TagAwareAdapterInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws CacheException
     */
    public function store(string $dataId, string $dataType, WPObject $object): bool
    {
        $realKey   = $this->getRealCacheKey($dataId, $dataType);
        $cacheItem = $this->cachePool->getItem($realKey);
        if ($cacheItem->isHit()) {
            return true;
        }
        $cacheItem->set($object);
        $cacheItem->tag($this->getCacheTags());

        $this->cachePool->save($cacheItem);

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function load(string $dataId, string $dataType): ?WPObject
    {
        try {
            $realKey   = $this->getRealCacheKey($dataId, $dataType);
            $cacheItem = $this->cachePool->getItem($realKey);
            if ($cacheItem->isHit()) {
                $this->info('found', ['dataId' => $dataId, 'dataType' => $dataType]);

                return $cacheItem->get();
            }
        } catch (\Exception $exception) {
            $this->error(
                __METHOD__,
                [
                    'dataId' => $dataId,
                    'dataType' => $dataType,
                    'e' => $exception,
                ]
            );
        }

        return null;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function clearAll(): void
    {
        $this->cachePool->invalidateTags($this->getCacheTags());
    }

    /**
     * @throws Exception
     */
    public function getCacheTags(): array
    {
        return [$this->normalizedCacheKey(self::NAMESPACE.'-'.$this->getBaseURl(self::NAMESPACE))];
    }

    /**
     * @throws Exception
     */
    private function getRealCacheKey(string $dataId, string $dataType): string
    {
        return $this->normalizedCacheKey(
            self::NAMESPACE.'-'.$this->getBaseURl(self::NAMESPACE).'-'.$dataId.'-'.$dataType
        );
    }

    private function normalizedCacheKey($cacheKey)
    {
        return str_replace(str_split(ItemInterface::RESERVED_CHARACTERS), '-', $cacheKey);
    }
}
