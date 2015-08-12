<?php

namespace ContinuousPipe\Adapter;

interface Adapter
{
    /**
     * Type of provider.
     *
     * @return string
     */
    public function getType();

    /**
     * Return the configuration class of the provider.
     *
     * @return string
     */
    public function getConfigurationClass();

    /**
     * Get provider repository.
     *
     * @return ProviderRepository
     */
    public function getRepository();

    /**
     * @return EnvironmentClientFactory
     */
    public function getEnvironmentClientFactory();
}
