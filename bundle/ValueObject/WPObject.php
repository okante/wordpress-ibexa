<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\ValueObject;

use DateTime;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @property-read int $id
 */
abstract class WPObject extends ValueObject
{
    protected static array $renderedAttributes = [];
    protected static array $dateAttributes     = [];
    protected int $id;

    public function __construct(array $properties = [])
    {
        foreach (static::$renderedAttributes as $property) {
            if (isset($properties[$property])) {
                $value                 = $properties[$property];
                $properties[$property] = is_array($value) ? ($value['rendered'] ?? '') : $value;
            }
        }
        foreach (static::$dateAttributes as $property) {
            if (isset($properties[$property])) {
                try {
                    $value = new DateTime($properties[$property] ?? null);
                } catch (Exception $exception) {
                    $value = new DateTime();
                }
                $properties[$property] = $value;
            }
        }
        parent::__construct($properties);
    }

    abstract public function getWPObjectTitle(): ?string;

    abstract public function getWPObjectId(): ?int;

    public function toArray(): array
    {
        $array = [];
        foreach ($this->getProperties() as $property => $propertyValue) {
            $array[$property] = $propertyValue;
        }

        return $array;
    }
}
