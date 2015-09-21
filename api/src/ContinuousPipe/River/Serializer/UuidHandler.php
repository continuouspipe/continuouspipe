<?php

namespace ContinuousPipe\River\Serializer;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\VisitorInterface;
use Rhumsaa\Uuid\Uuid;

class UuidHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => Uuid::class,
                'format' => 'json',
                'method' => 'serializeUuid',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => Uuid::class,
                'format' => 'json',
                'method' => 'deserializeUuid',
            ],
        ];
    }

    /**
     * Serialize the UUIDs to string.
     *
     * @param JsonSerializationVisitor $visitor
     * @param Uuid                     $uuid
     *
     * @return string
     */
    public function serializeUuid(JsonSerializationVisitor $visitor, Uuid $uuid)
    {
        return (string) $uuid;
    }

    /**
     * Deserialize UUID from string.
     *
     * @param VisitorInterface $visitor
     * @param string           $string
     *
     * @return Uuid
     */
    public function deserializeUuid(VisitorInterface $visitor, $string)
    {
        return Uuid::fromString($string);
    }
}
