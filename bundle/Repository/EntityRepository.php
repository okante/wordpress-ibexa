<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class EntityRepository.
 */
abstract class EntityRepository extends ServiceEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getEntityClass());
    }

    /**
     * Get the EntityClass.
     */
    abstract protected function getEntityClass(): string;

    protected function createQB(): QueryBuilder
    {
        return $this->createQueryBuilder($this->getAlias())->select($this->getAlias())->distinct();
    }

    /**
     * Get the Alias.
     */
    abstract protected function getAlias(): string;
}
