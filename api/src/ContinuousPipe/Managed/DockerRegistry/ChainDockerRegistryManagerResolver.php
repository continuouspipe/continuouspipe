<?php

namespace ContinuousPipe\Managed\DockerRegistry;

class ChainDockerRegistryManagerResolver implements DockerRegistryManagerResolver
{
    /**
     * @var array|DockerRegistryManagerResolver[]
     */
    private $resolvers;

    /**
     * @param DockerRegistryManagerResolver[] $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $dsn): DockerRegistryManager
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($dsn)) {
                return $resolver->get($dsn);
            }
        }

        throw new DockerRegistryException('No Docker Registry manager available for the given DSN');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $dsn): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($dsn)) {
                return true;
            }
        }

        return false;
    }
}
