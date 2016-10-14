<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Serializer;

use GitHub\WebHook\Event;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\VisitorInterface;

class GitHubEventSeralizerHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => WrappedGitHubEvent::class,
                'method' => 'serializeGitHubEvent',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => WrappedGitHubEvent::class,
                'method' => 'deserializeGitHubEvent',
            ],
        ];
    }

    public function serializeGitHubEvent(JsonSerializationVisitor $visitor, WrappedGitHubEvent $wrappedEvent, array $type, Context $context)
    {
        $event = $wrappedEvent->getEvent();

        $envelope = [
            'event' => $event,
            'eventType' => get_class($event),
        ];

        return $visitor->getNavigator()->accept($envelope, null, $context);
    }

    public function deserializeGitHubEvent(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        $eventType = $data['eventType'];

        return $context->accept($data['event'], ['name' => $eventType, 'params' => []]);
    }
}
