<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\UuidUpgrade;

use Ramsey\Uuid\Uuid;
use Rhumsaa\Uuid\Uuid as RhumsaaUuid;

class UuidReplacer
{
    public static function replace($object)
    {
        if (is_array($object)) {
            return array_map(function ($child) {
                return self::replace($child);
            }, $object);
        } elseif (!is_object($object)) {
            return $object;
        }

        if ($object instanceof RhumsaaUuid) {
            return Uuid::fromString($object->toString());
        }

        $reflectionObject = new \ReflectionObject($object);
        $properties = $reflectionObject->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyValue = $property->getValue($object);

            if ($propertyValue !== null) {
                $property->setValue($object, self::replace($propertyValue));
            }
        }

        return $object;
    }
}
