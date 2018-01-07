<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

use malkusch\lock\exception\MutexException;
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
     * @param int    $timeout
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
        $mutex = new Mutex([$this->client], $name, $this->timeout);

        try {
            return $mutex->synchronized($callable);
        } catch (MutexException $e) {
            throw new LockerException('Unable to synchronized the operation', $e->getCode(), $e);
        }
    }
}
