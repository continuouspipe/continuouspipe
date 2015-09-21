<?php

namespace ContinuousPipe\River\Task\Deploy\Serializer;

use ContinuousPipe\River\ArrayContext;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class DeployContextSerializerHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => DeployContext::class,
                'format' => 'json',
                'method' => 'serializeContext',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => DeployContext::class,
                'format' => 'json',
                'method' => 'deserializeContext',
            ],
        ];
    }

    /**
     * Serialize the UUIDs to string.
     *
     * @param JsonSerializationVisitor $visitor
     * @param DeployContext            $context
     *
     * @return string
     */
    public function serializeContext(JsonSerializationVisitor $visitor, DeployContext $context)
    {
        return base64_encode(serialize($context));
    }

    /**
     * Deserialize UUID from string.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param string                     $string
     *
     * @return DeployContext
     */
    public function deserializeContext(JsonDeserializationVisitor $visitor, $string)
    {
        return unserialize(base64_decode($string));
    }
}
