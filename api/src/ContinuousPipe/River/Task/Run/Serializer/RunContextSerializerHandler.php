<?php

namespace ContinuousPipe\River\Task\Run\Serializer;

use ContinuousPipe\River\ArrayContext;
use ContinuousPipe\River\Task\Run\RunContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
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
        return json_encode($context->getBag());
    }

    /**
     * Deserialize UUID from string.
     *
     * @param JsonSerializationVisitor $visitor
     * @param string                   $string
     *
     * @return RunContext
     */
    public function deserializeContext(JsonSerializationVisitor $visitor, $string)
    {
        return new RunContext(ArrayContext::fromRaw(json_decode($string, true)));
    }
}
