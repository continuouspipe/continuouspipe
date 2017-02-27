<?php

namespace ContinuousPipe\DevelopmentEnvironmentBundle\Request;

use JMS\Serializer\Annotation as JMS;

class EnvironmentCreationRequest
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
