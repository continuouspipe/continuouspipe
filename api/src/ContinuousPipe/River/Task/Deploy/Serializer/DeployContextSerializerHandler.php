<?php

namespace ContinuousPipe\River\Task\Deploy\Serializer;

use ContinuousPipe\River\ArrayContext;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
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
        return json_encode($context->getBag());
    }

    /**
     * Deserialize UUID from string.
     *
     * @param JsonSerializationVisitor $visitor
     * @param string                   $string
     *
     * @return DeployContext
     */
    public function deserializeContext(JsonSerializationVisitor $visitor, $string)
    {
        return new DeployContext(ArrayContext::fromRaw(json_decode($string, true)));
    }
}
