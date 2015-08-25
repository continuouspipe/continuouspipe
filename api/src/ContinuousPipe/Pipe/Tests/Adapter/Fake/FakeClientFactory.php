<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake;

use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Provider;

class FakeClientFactory implements EnvironmentClientFactory
{
    /**
     * @var FakeEnvironmentClient
     */
    private $fakeEnvironmentClient;

    /**
     * @param FakeEnvironmentClient $fakeEnvironmentClient
     */
    public function __construct(FakeEnvironmentClient $fakeEnvironmentClient)
    {
        $this->fakeEnvironmentClient = $fakeEnvironmentClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getByProvider(Provider $provider)
    {
        return $this->fakeEnvironmentClient;
    }
}
