<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Adapter\Provider;

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
     * {@inheritdoc}
     */
    public function getAdapterType()
    {
        return 'fake';
    }
}
