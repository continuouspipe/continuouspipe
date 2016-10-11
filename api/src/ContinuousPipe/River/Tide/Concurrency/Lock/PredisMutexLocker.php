<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

use malkusch\lock\mutex\PredisMutex;
use Predis\Client;

class PredisMutexLocker implements Locker
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @param Client $client
     * @param int $timeout
     */
    public function __construct(Client $client, $timeout)
    {
        $this->client = $client;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function lock($name, callable $callable)
    {
        $mutex = new PredisMutex([$this->client], $name, $this->timeout);

        return $mutex->synchronized($callable);
    }
}
