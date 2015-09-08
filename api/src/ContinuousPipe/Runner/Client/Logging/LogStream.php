<?php

namespace ContinuousPipe\Runner\Client\Logging;

use JMS\Serializer\Annotation as JMS;

class LogStream
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $parentLogIdentifier;

    /**
     * @param string $parentLogIdentifier
     */
    public function __construct($parentLogIdentifier)
    {
        $this->parentLogIdentifier = $parentLogIdentifier;
    }

    /**
     * @return string
     */
    public function getParentLogIdentifier()
    {
        return $this->parentLogIdentifier;
    }
}
