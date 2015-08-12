<?php

namespace ContinuousPipe\Adapter;

interface Provider
{
    /**
     * Get provider identifier.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get the type of adapter.
     *
     * @return string
     */
    public function getAdapterType();
}
