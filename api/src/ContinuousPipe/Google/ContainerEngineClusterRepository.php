<?php

namespace ContinuousPipe\Google;

use ContinuousPipe\Security\Account\GoogleAccount;

interface ContainerEngineClusterRepository
{
    /**
     * @param GoogleAccount $account
     * @param string        $project
     *
     * @return ContainerEngineCluster[]
     */
    public function findAll(GoogleAccount $account, string $project);
}
