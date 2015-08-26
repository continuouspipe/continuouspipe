<?php

namespace ContinuousPipe\Adapter;

interface ProviderRepository
{
    /**
     * @param Provider $provider
     *
     * @return Provider
     */
    public function create(Provider $provider);

    /**
     * @return Provider[]
     */
    public function findAll();

    /**
     * @param string $identifier
     *
     * @throws ProviderNotFound
     *
     * @return Provider
     */
    public function find($identifier);

    /**
     * Remove the given provider.
     *
     * @param Provider $provider
     *
     * @throws ProviderNotFound
     *
     * @return Provider
     */
    public function remove(Provider $provider);
}
