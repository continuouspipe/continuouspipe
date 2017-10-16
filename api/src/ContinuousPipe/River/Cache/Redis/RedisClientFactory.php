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

        if (strpos($dsn, ':') === false) {
            $dsn = $dsn.':6379';
        }

        if (strpos($dsn, '://') === false) {
            $dsn = 'tcp://'.$dsn;
        }

        return new Client($dsn);
    }
}
