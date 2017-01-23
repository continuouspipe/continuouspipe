<?php

namespace ContinuousPipe\Message\RabbitMq;

use ContinuousPipe\Message\Message;
use ContinuousPipe\Message\MessageClassMapping;
use ContinuousPipe\Message\MessageConsumer;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RabbitMqConsumerToMessageConsumer implements ConsumerInterface
{
    /**
     * @var MessageConsumer
     */
    private $messageConsumer;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MessageConsumer $messageConsumer
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(MessageConsumer $messageConsumer, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->messageConsumer = $messageConsumer;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $this->messageConsumer->consume(
            $this->unserialize($msg)
        );
    }

    /**
     * @param AMQPMessage $msg
     *
     * @throws MessageParsingException
     *
     * @return Message
     */
    private function unserialize(AMQPMessage $msg) : Message
    {
        $headers = $msg->get('application_headers');
        if (!isset($headers['X-Message-Name'])) {
            throw new MessageParsingException('Unable to get the message name from the AMQP message');
        }

        try {
            $message = $this->serializer->deserialize(
                $msg->getBody(),
                MessageClassMapping::fromName($headers['X-Message-Name']),
                'json'
            );
        } catch (\Exception $e) {
            throw new MessageParsingException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$message instanceof Message) {
            throw new MessageParsingException('Unserialized message do not implements the `Message` interface');
        }

        return $message;
    }
}
