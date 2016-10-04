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
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function lock($name, callable $callable)
    {
        $mutex = new PredisMutex([$this->client], $name);

        return $mutex->synchronized($callable);
    }
}
