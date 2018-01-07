<?php

namespace spec\ContinuousPipe\River\Infrastructure\Doctrine\Type;

use ContinuousPipe\River\Infrastructure\Doctrine\Type\UTCDateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UTCDateTimeTypeSpec extends ObjectBehavior
{
    function let()
    {
        if (!Type::hasType('utcdatetime')) {
            Type::addType('utcdatetime', UTCDateTimeType::class);
        }

        $this->beConstructedThrough('getType', ['utcdatetime']);
    }

    function it_can_converts_back_dates_with_micro_seconds(AbstractPlatform $platform)
    {
        $this->convertToPHPValue('2017-02-09 20:56:50.475200', $platform)->shouldBeLike(
            \DateTime::createFromFormat('Y-m-d H:i:s.u', '2017-02-09 20:56:50.475200')
        );
    }

    function it_can_converts_back_dates_without_micro_seconds(AbstractPlatform $platform)
    {
        $this->convertToPHPValue('2017-02-09 20:56:50', $platform)->shouldBeLike(
            \DateTime::createFromFormat('Y-m-d H:i:s', '2017-02-09 20:56:50')
        );
    }

    function it_throws_an_exception_for_non_supported_formats(AbstractPlatform $platform)
    {
        $this->shouldThrow(ConversionException::class)->duringConvertToPHPValue('2017-02-09', $platform);
    }
}
