<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake;

use ContinuousPipe\Adapter\ClusterNotSupported;
use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Pipe\Tests\Cluster\TestCluster;
use ContinuousPipe\Security\Credentials\Cluster;

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
    public function getByCluster(Cluster $cluster)
    {
        if (!$cluster instanceof TestCluster) {
            throw new ClusterNotSupported('Only test clusters are supported here');
        }

        return $this->fakeEnvironmentClient;
    }
}
