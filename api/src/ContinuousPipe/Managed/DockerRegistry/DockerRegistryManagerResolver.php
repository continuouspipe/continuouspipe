<?php

namespace ContinuousPipe\Managed\DockerRegistry;

interface DockerRegistryManagerResolver
{
    /**
     * @param string $dsn
     *
     * @throws DockerRegistryException
     *
     * @return DockerRegistryManager
     */
    public function get(string $dsn) : DockerRegistryManager;

    /**
     * Return true if supports the given DSN.
     *
     * @param string $dsn
     *
     * @return bool
     */
    public function supports(string $dsn) : bool;
}
