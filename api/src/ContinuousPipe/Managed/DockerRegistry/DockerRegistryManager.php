<?php

namespace ContinuousPipe\Managed\DockerRegistry;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\DockerRegistry;

interface DockerRegistryManager
{
    /**
     * @param FlatFlow $flow
     * @param string $visibility
     *
     * @throws DockerRegistryException
     *
     * @return DockerRegistry
     */
    public function createRepositoryForFlow(FlatFlow $flow, string $visibility);

    /**
     * @param FlatFlow $flow
     * @param DockerRegistry $registry
     * @param string $visibility
     *
     * @throws DockerRegistryException
     */
    public function changeVisibility(FlatFlow $flow, DockerRegistry $registry, string $visibility);
}
