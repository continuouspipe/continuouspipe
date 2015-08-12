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
}
