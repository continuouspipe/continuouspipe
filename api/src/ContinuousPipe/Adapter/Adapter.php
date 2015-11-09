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
     * @return EnvironmentClientFactory
     */
    public function getEnvironmentClientFactory();
}
