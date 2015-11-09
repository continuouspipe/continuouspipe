<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\AdapterRegistry;
use ContinuousPipe\Adapter\ClusterNotSupported;
use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Security\Credentials\Cluster;

class ChainEnvironmentClientFactory implements EnvironmentClientFactory
{
    /**
     * @var AdapterRegistry
     */
    private $adapterRegistry;

    /**
     * @param AdapterRegistry $adapterRegistry
     */
    public function __construct(AdapterRegistry $adapterRegistry)
    {
        $this->adapterRegistry = $adapterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCluster(Cluster $cluster)
    {
        foreach ($this->adapterRegistry->getAdapters() as $adapter) {
            try {
                return $adapter->getEnvironmentClientFactory()->getByCluster($cluster);
            } catch (ClusterNotSupported $e) {
                continue;
            }
        }

        throw new ClusterNotSupported(sprintf(
            'Cluster of type %s is not supported',
            get_class($cluster)
        ));
    }
}
