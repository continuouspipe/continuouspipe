<?php

namespace ContinuousPipe\River\Serializer;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
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
                'method' => 'serializeUuid'
            ]
        ];
    }

    /**
     * Serialize the UUIDs to string.
     *
     * @param JsonSerializationVisitor $visitor
     * @param Uuid $uuid
     * @return string
     */
    public function serializeUuid(JsonSerializationVisitor $visitor, Uuid $uuid)
    {
        return (string) $uuid;
    }
}
