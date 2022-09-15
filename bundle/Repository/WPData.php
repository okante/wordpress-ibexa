<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Repository;

use Almaviacx\Bundle\Ibexa\WordPress\Entity\WPData as WPDataEntity;

/**
 * Class Pim.
 */
class WPData extends EntityRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getAlias(): string
    {
        return 'd';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return WPDataEntity::class;
    }
}
