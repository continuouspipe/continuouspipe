<?php

use Behat\Behat\Context\Context;
use Predis\ClientInterface;

class CacheContext implements Context
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Given the cache is clean
     */
    public function theCacheIsClean()
    {
        $this->client->flushall();
    }
}
