<?php

namespace GitHub\Integration;

class TraceableInstallationTokenResolver implements InstallationTokenResolver
{

    /**
     * @var InstallationTokenResolver
     */
    private $decoratedInstallationTokenResolver;

    /**
     * @var int
     */
    private $apiCallCount = 0;

    public function __construct(InstallationTokenResolver $decoratedInstallationTokenResolver)
    {
        $this->decoratedInstallationTokenResolver = $decoratedInstallationTokenResolver;
    }

    /**
     * @param Installation $installation
     *
     * @throws InstallationTokenException
     *
     * @return InstallationToken
     */
    public function get(Installation $installation)
    {
        ++$this->apiCallCount;

        return $this->decoratedInstallationTokenResolver->get($installation);
    }

    public function countApiCalls(): int
    {
        return $this->apiCallCount;
    }
}
