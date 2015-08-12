<?php

namespace ContinuousPipe\Adapter;

interface EnvironmentClientFactory
{
    /**
     * @param Provider $provider
     *
     * @return EnvironmentClient
     */
    public function getByProvider(Provider $provider);
}
