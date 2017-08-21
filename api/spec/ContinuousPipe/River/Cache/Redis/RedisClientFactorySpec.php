<?php

namespace spec\ContinuousPipe\River\Cache\Redis;

use ContinuousPipe\River\Cache\Redis\RedisClientFactory;
use PhpSpec\ObjectBehavior;
use Predis\Client;
use Predis\Connection\Aggregate\SentinelReplication;
use Prophecy\Argument;

class RedisClientFactorySpec extends ObjectBehavior
{
    function it_create_a_simple_redis_client()
    {
        $this::create('redis')->shouldBeLike(new Client('redis'));
    }

    function it_creates_a_sentinel_configured_client()
    {
        $this::create('redis-sentinel-0.redis-sentinel-headless.stack-master.svc.cluster.local:26379,redis-sentinel-1.redis-sentinel-headless.stack-master.svc.cluster.local:26379,redis-sentinel-2.redis-sentinel-headless.stack-master.svc.cluster.local:26379')
            ->shouldBeSentinelReady();
    }

    public function getMatchers() : array
    {
        return [
            'beSentinelReady' => function ($subject) {
                /** @var \Predis\Client $subject */
                return $subject->getConnection() instanceof SentinelReplication;
            }
        ];
    }
}
