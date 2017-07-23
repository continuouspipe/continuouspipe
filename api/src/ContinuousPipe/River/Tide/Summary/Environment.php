<?php

namespace ContinuousPipe\River\Tide\Summary;

class Environment
{
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string
     */
    private $cluster;

    public function __construct(string $identifier, string $cluster)
    {
        $this->identifier = $identifier;
        $this->cluster = $cluster;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getCluster(): string
    {
        return $this->cluster;
    }

    public function __toString()
    {
        return 'Cluster: '.$this->getCluster().' Identifier: '.$this->getIdentifier();
    }
}
