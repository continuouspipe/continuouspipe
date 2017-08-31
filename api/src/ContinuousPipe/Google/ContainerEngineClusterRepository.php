<?php

namespace ContinuousPipe\Google;

use ContinuousPipe\Security\Account\GoogleAccount;

interface ContainerEngineClusterRepository
{
    /**
     * @param GoogleAccount $account
     * @param string        $project
     *
     * @throws GoogleException
     *
     * @return ContainerEngineCluster[]
     */
    public function findAll(GoogleAccount $account, string $project);

    /**
     * @param GoogleAccount $account
     * @param string $project
     * @param string $clusterIdentifier
     *
     * @throws GoogleException
     *
     * @return ContainerEngineCluster
     */
    public function find(GoogleAccount $account, string $project, string $clusterIdentifier) : ContainerEngineCluster;
}
