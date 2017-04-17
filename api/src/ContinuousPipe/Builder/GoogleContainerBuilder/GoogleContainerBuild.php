<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use JMS\Serializer\Annotation as JMS;

class GoogleContainerBuild
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
