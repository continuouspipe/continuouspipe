<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class UTCDateTimeType extends DateTimeType
{
    private static $utc;

    /**
     * @param \DateTime        $value
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTime) {
            $value->setTimezone(self::getUtc());
        }

        return $value !== null ? $value->format($this->getDateTimeFormatString()) : null;
    }

    /**
     * @param string           $value
     * @param AbstractPlatform $platform
     *
     * @return \DateTime
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $formats = [
            $this->getDateTimeFormatString(),
            'Y-m-d H:i:s',
        ];

        $converted = false;
        foreach ($formats as $format) {
            if (
                $converted = \DateTime::createFromFormat(
                    $format,
                    $value,
                    self::getUTC()
                )
            ) {
                break;
            }
        }

        if (!$converted) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $this->getDateTimeFormatString()
            );
        }

        return $converted;
    }

    /**
     * @return string
     */
    private function getDateTimeFormatString()
    {
        return 'Y-m-d H:i:s.u';
    }

    /**
     * @return \DateTimeZone
     */
    private static function getUTC()
    {
        if (self::$utc === null) {
            self::$utc = new \DateTimeZone('UTC');
        }

        return self::$utc;
    }
}
