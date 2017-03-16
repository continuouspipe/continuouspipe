<?php

namespace GitHub\Integration;

class InMemoryInstallationTokenResolver implements InstallationTokenResolver
{
    private $tokenByInstallation = [];

    private $apiCallsCount = 0;

    /**
     * {@inheritdoc}
     */
    public function get(Installation $installation)
    {
        if (!array_key_exists($installation->getId(), $this->tokenByInstallation)) {
            throw new InstallationTokenException('Installation token not found');
        }

        ++$this->apiCallsCount;

        return $this->tokenByInstallation[$installation->getId()];
    }

    /**
     * @param integer $installationId
     * @param InstallationToken $token
     */
    public function addToken($installationId, InstallationToken $token)
    {
        $this->tokenByInstallation[$installationId] = $token;
    }

    public function countApiCalls(): int
    {
        return $this->apiCallsCount;
    }
}
