<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\VarDateTimeType;

class PostgresMicroDateTimeType extends VarDateTimeType
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'TIMESTAMP(6) WITHOUT TIME ZONE';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value !== null ? $value->format('Y-m-d\TH:i:s.uO') : null;
    }
}
