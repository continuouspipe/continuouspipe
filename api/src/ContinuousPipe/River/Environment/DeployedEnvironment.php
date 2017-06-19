<?php

namespace ContinuousPipe\River\Environment;

use ContinuousPipe\Model\Component;
use JMS\Serializer\Annotation as JMS;

class DeployedEnvironment
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $identifier;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $cluster;

    /**
     * @JMS\Type("array<ContinuousPipe\Model\Component>")
     *
     * @var Component[]
     */
    private $components = [];

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @param string $identifier
     * @param string $cluster
     * @param Component[] $components
     * @param string $status
     */
    public function __construct($identifier, $cluster, array $components = [], string $status = null)
    {
        $this->identifier = $identifier;
        $this->cluster = $cluster;
        $this->components = $components;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getCluster()
    {
        return $this->cluster;
    }
}
