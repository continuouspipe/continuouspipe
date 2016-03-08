<?php

namespace ContinuousPipe\River\Infrastructure\RabbitMq;

use ContinuousPipe\River\CommandBus\DelayedCommandBus;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Connection\AbstractConnection;
use SimpleBus\Asynchronous\Properties\AdditionalPropertiesResolver;
use SimpleBus\Asynchronous\Routing\RoutingKeyResolver;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;

class RabbitMqDelayedMessageProducer implements DelayedCommandBus
{
    /**
     * @var MessageInEnvelopSerializer
     */
    private $messageSerializer;

    /**
     * @var RoutingKeyResolver
     */
    private $routingKeyResolver;

    /**
     * @var AdditionalPropertiesResolver
     */
    private $additionalPropertiesResolver;

    /**
     * @var AbstractConnection
     */
    private $amqpConnection;

    /**
     * @param MessageInEnvelopSerializer   $messageSerializer
     * @param RoutingKeyResolver           $routingKeyResolver
     * @param AdditionalPropertiesResolver $additionalPropertiesResolver
     * @param AbstractConnection           $amqpConnection
     */
    public function __construct(MessageInEnvelopSerializer $messageSerializer, RoutingKeyResolver $routingKeyResolver, AdditionalPropertiesResolver $additionalPropertiesResolver, AbstractConnection $amqpConnection)
    {
        $this->messageSerializer = $messageSerializer;
        $this->routingKeyResolver = $routingKeyResolver;
        $this->additionalPropertiesResolver = $additionalPropertiesResolver;
        $this->amqpConnection = $amqpConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($command, $delay)
    {
        $serializedMessage = $this->messageSerializer->wrapAndSerialize($command);
        $routingKey = $this->routingKeyResolver->resolveRoutingKeyFor($command);
        $additionalProperties = $this->additionalPropertiesResolver->resolveAdditionalPropertiesFor($command);

        $producer = new Producer($this->amqpConnection);
        $expiration = 1000 + floor(1.1 * $delay);

        $name = 'delay-exchange';
        $id = sprintf('delay-waiting-queue-%s-%d', $routingKey, $delay);

        $producer->setExchangeOptions(array(
            'name' => $name,
            'type' => 'direct',
        ));

        $producer->setQueueOptions(array(
            'name' => $id,
            'routing_keys' => array($id),
            'arguments' => array(
                'x-message-ttl' => array('I', $delay),
                'x-dead-letter-exchange' => array('S', 'river_commands'),
                'x-dead-letter-routing-key' => array('S', $routingKey),
                'x-expires' => array('I', $expiration),
            ),
        ));

        $producer->setupFabric();
        $producer->publish($serializedMessage, $id, $additionalProperties);
    }
}
