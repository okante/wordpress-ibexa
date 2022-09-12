<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

use DateTimeImmutable;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @property-read int $id
 */
abstract class WPObject extends ValueObject
{
    protected static array $renderedAttributes = [];
    protected static array $dateAttributes = [];
    protected int $id;

    public function __construct(array $properties = [])
    {
        foreach (static::$renderedAttributes as $property) {
            if (isset($properties[$property])) {
                $value = $properties[$property];
                $properties[$property] = is_array($value) ? ($value['rendered']?? ''): $value;
            }
        }
        foreach (static::$dateAttributes as $property) {
            if (isset($properties[$property])) {
                try {
                    $value = new DateTimeImmutable($properties[$property]?? null);
                } catch (Exception $exception) {
                    $value = new DateTimeImmutable();
                }
                $properties[$property] = $value;
            }
        }
        parent::__construct($properties);
    }
}