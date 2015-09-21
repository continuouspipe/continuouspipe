<?php

namespace ContinuousPipe\River\Task\Run\Serializer;

use ContinuousPipe\River\ArrayContext;
use ContinuousPipe\River\Task\Run\RunContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class RunContextSerializerHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => RunContext::class,
                'format' => 'json',
                'method' => 'serializeContext',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => RunContext::class,
                'format' => 'json',
                'method' => 'deserializeContext',
            ],
        ];
    }

    /**
     * Serialize the UUIDs to string.
     *
     * @param JsonSerializationVisitor $visitor
     * @param RunContext               $context
     *
     * @return string
     */
    public function serializeContext(JsonSerializationVisitor $visitor, RunContext $context)
    {
        return base64_encode(serialize($context));
    }

    /**
     * Deserialize UUID from string.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param string                     $string
     *
     * @return RunContext
     */
    public function deserializeContext(JsonDeserializationVisitor $visitor, $string)
    {
        return unserialize(base64_decode($string));
    }
}
