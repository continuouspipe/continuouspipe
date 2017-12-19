<?php

namespace ContinuousPipe\Security\Credentials\Cluster;

class ClusterPolicy
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var array<string,string>
     */
    private $secrets;

    public function __construct(string $name, array $configuration = [], array $secrets = [])
    {
        $this->name = $name;
        $this->configuration = $configuration;
        $this->secrets = $secrets;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration ?: [];
    }

    /**
     * @return array
     */
    public function getSecrets(): array
    {
        return $this->secrets ?: [];
    }
}
