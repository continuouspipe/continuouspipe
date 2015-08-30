<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\ObjectType;
use Doctrine\DBAL\Types\Type;

class Base64Object extends ObjectType
{
    const TYPE = 'b64Object';

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return base64_encode(serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        $val = unserialize(base64_decode($value));
        if ($val === false && $value !== 'b:0;') {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $val;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::TYPE;
    }
}
