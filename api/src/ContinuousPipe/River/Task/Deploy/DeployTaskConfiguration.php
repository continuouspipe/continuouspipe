<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Model\Component;
use JMS\Serializer\Annotation as JMS;

class DeployTaskConfiguration
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $providerName;

    /**
     * @JMS\Type("array<string, ContinuousPipe\Model\Component>")
     *
     * @var Component[]
     */
    private $services;

    /**
     * @param string      $providerName
     * @param Component[] $services
     */
    public function __construct($providerName, array $services)
    {
        $this->providerName = $providerName;
        $this->services = $services;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return \ContinuousPipe\Model\Component[]
     */
    public function getComponents()
    {
        return $this->services;
    }
}
