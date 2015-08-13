<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Model\Environment;
use LogStream\Logger;

class FakeEnvironmentClient implements EnvironmentClient
{
    /**
     * @var array
     */
    private $createdOrUpdated = [];

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(Environment $environment, Logger $logger)
    {
        $this->createdOrUpdated[] = $environment;

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->createdOrUpdated;
    }

    /**
     * @return array
     */
    public function getCreatedOrUpdated()
    {
        return $this->createdOrUpdated;
    }
}
