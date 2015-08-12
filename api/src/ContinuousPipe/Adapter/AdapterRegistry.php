<?php

namespace ContinuousPipe\Adapter;

class AdapterRegistry
{
    /**
     * @var Adapter[]
     */
    private $adapters;

    /**
     * @param Adapter[] $adapters
     */
    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    /**
     * Register a new adapter.
     *
     * @param Adapter $adapter
     */
    public function register(Adapter $adapter)
    {
        $this->adapters[] = $adapter;
    }

    /**
     * @param string $type
     *
     * @return Adapter
     *
     * @throws AdapterNotFound
     */
    public function getByType($type)
    {
        foreach ($this->adapters as $adapter) {
            if ($type === $adapter->getType()) {
                return $adapter;
            }
        }

        throw new AdapterNotFound(sprintf('Adapter of type "%s" not found', $type));
    }

    /**
     * @return Adapter[]
     */
    public function getAdapters()
    {
        return $this->adapters;
    }
}
