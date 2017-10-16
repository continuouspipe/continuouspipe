<?php

namespace spec\ContinuousPipe\River\Cache\Redis;

use ContinuousPipe\River\Cache\Redis\RedisClientFactory;
use PhpSpec\ObjectBehavior;
use Predis\Client;
use Predis\Connection\Aggregate\SentinelReplication;
use Prophecy\Argument;

class RedisClientFactorySpec extends ObjectBehavior
{
    function it_uses_the_dsn()
    {
        $this::create('tcp://redis:6379')->shouldBeLike(new Client('tcp://redis:6379'));
    }

    function it_adds_the_protocol_and_port()
    {
        $this::create('redis')->shouldBeLike(new Client('tcp://redis:6379'));
    }

    function it_creates_a_sentinel_configured_client()
    {
        $this::create('redis-sentinel-0.redis-sentinel-headless.stack-master.svc.cluster.local:26379,redis-sentinel-1.redis-sentinel-headless.stack-master.svc.cluster.local:26379,redis-sentinel-2.redis-sentinel-headless.stack-master.svc.cluster.local:26379')
            ->shouldBeSentinelReady();
    }

    function it_uses_redis_procotol_if_given()
    {
        $this::create('redis://admin:password@hostname.10.dblayer.com:17710')->shouldBeLike(new Client('redis://admin:password@hostname.10.dblayer.com:17710'));;
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
