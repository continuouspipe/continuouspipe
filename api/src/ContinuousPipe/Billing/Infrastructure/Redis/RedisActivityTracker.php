<?php

namespace ContinuousPipe\Billing\Infrastructure\Redis;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Message\UserActivity;
use JMS\Serializer\SerializerInterface;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Exception\Exception as JMSException;

class RedisActivityTracker implements ActivityTracker
{
    /**
     * @var ClientInterface
     */
    private $redisClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientInterface $redisClient
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(ClientInterface $redisClient, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->redisClient = $redisClient;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function track(UserActivity $userActivity)
    {
        $key = sprintf(
            'activity:%s:%s:%s:%s:%s',
            (string) $userActivity->getFlowUuid(),
            $userActivity->getDateTime()->format('Y'),
            $userActivity->getDateTime()->format('m'),
            $userActivity->getDateTime()->format('d'),
            $userActivity->getUser()->getUsername()
        );

        $this->redisClient->set(
            $key,
            $this->serializer->serialize($userActivity, 'json')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(UuidInterface $flowUuid, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $keys = $this->redisClient->keys('activity:'.$flowUuid->toString().':*');
        $activities = [];

        foreach ($keys as $key) {
            $message = $this->redisClient->get($key);

            try {
                $activities[] = $this->serializer->deserialize($message, UserActivity::class, 'json');
            } catch (JMSException $e) {
                $this->logger->error('Unable to deserialize the user activity', [
                    'key' => $key,
                ]);
            }
        }

        return $activities;
    }
}
