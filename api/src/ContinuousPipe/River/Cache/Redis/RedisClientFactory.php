<?php

namespace ContinuousPipe\River\Cache\Redis;

use Predis\Client;

class RedisClientFactory
{
    public static function create(string $dsn)
    {
        if (strpos($dsn, ',') !== false) {
            return new Client(
                explode(',', $dsn),
                [
                    'replication' => 'sentinel',
                    'service' => 'mymaster',
                ]
            );
        }

        return new Client($dsn);
    }
}
