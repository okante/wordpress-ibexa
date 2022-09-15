<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @property-read int $order
 * @property-read string $field
 */
class OrderBy extends ValueObject
{
    public const DIR_ASC = 'asc';
    public const DIR_DESC = 'desc';
    protected ?string $dir;
    protected ?string $field;

    public function __construct(array $properties = [])
    {
        parent::__construct(
            [
                'dir' => ($properties['dir'] ?? self::DIR_ASC) === self::DIR_DESC ? self::DIR_DESC : self::DIR_ASC,
                'field' => $properties['field'] ?? null,
            ]
        );
    }

    public function format(): ?array
    {
        if (empty($this->orderBy)) {
            if (!empty($orderBy->field)) {
                return ['orderby' => $orderBy->field,
                    'order' => $orderBy->dir
                ];
            }
        }
        return [];
    }
}