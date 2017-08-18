<?php

namespace ContinuousPipe\River\Flex;

use JMS\Serializer\Annotation as JMS;

class FlexConfiguration
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $smallIdentifier;

    /**
     * @param string $smallIdentifier
     */
    public function __construct(string $smallIdentifier)
    {
        $this->smallIdentifier = $smallIdentifier;
    }

    /**
     * @return string|null
     */
    public function getSmallIdentifier()
    {
        return $this->smallIdentifier;
    }
}
