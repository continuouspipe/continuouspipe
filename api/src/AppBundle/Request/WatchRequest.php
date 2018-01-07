<?php

namespace AppBundle\Request;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class WatchRequest
{
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $cluster;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $environment;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $pod;

    /**
     * @return string
     */
    public function getCluster(): string
    {
        return $this->cluster;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getPod(): string
    {
        return $this->pod;
    }
}
