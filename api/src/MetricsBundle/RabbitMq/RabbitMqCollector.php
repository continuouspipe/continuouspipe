<?php

namespace MetricsBundle\RabbitMq;

use MetricsBundle\Collector\MetricCollector;

class RabbitMqCollector implements MetricCollector
{
    /**
     * @var RabbitMqHttpClient
     */
    private $rabbitMqClient;

    /**
     * @var string
     */
    private $vhost;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @param RabbitMqHttpClient $rabbitMqClient
     * @param string $vhost
     * @param string $queueName
     */
    public function __construct(RabbitMqHttpClient $rabbitMqClient, $vhost, $queueName)
    {
        $this->rabbitMqClient = $rabbitMqClient;
        $this->vhost = $vhost;
        $this->queueName = $queueName;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $queue = $this->rabbitMqClient->getQueue($this->vhost, $this->queueName);

        return [
            'pending' => $queue['messages_ready'],
            'running' => $queue['messages_unacknowledged'],
        ];
    }
}
