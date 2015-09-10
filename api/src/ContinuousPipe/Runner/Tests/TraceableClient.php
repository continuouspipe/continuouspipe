<?php

namespace ContinuousPipe\Runner\Tests;

use ContinuousPipe\Runner\Client;
use ContinuousPipe\Runner\Client\RunRequest;
use ContinuousPipe\User\User;
use Rhumsaa\Uuid\Uuid;

class TraceableClient implements Client
{
    /**
     * @var RunRequest[]
     */
    private $requests;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Uuid
     */
    private $lastUuid;

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
    public function run(RunRequest $request, User $user)
    {
        $this->requests[] = $request;

        $this->lastUuid = $this->client->run($request, $user);

        return $this->lastUuid;
    }

    /**
     * @return Client\RunRequest[]
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @return Uuid
     */
    public function getLastUuid()
    {
        return $this->lastUuid;
    }
}
