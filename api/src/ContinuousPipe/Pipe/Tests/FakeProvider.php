<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Adapter\Provider;
use JMS\Serializer\Annotation as JMS;

class FakeProvider implements Provider
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     *
     * {@inheritdoc}
     */
    public function getAdapterType()
    {
        return 'fake';
    }
}
