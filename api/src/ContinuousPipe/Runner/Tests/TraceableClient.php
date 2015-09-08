<?php

namespace ContinuousPipe\Runner\Tests;

use ContinuousPipe\Runner\Client;
use ContinuousPipe\Runner\Client\RunRequest;
use ContinuousPipe\User\User;

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

        return $this->client->run($request, $user);
    }

    /**
     * @return Client\RunRequest[]
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
