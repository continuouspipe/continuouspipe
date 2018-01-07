<?php

namespace ContinuousPipe\UserActivity;

use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMqUserActivityDispatcher implements UserActivityDispatcher
{
    private $producer;
    private $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function dispatch(UserActivity $userActivity)
    {
        $this->producer->publish(
            $this->serializer->serialize($userActivity, 'json'),
            '',
            [
                'application_headers' => new AMQPTable([
                    'X-Message-Name' => 'user_activity',
                ]),
            ]
        );
    }
}
