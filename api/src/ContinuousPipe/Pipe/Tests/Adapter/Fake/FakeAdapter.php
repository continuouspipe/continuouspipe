<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake;

use ContinuousPipe\Adapter\Adapter;

class FakeAdapter implements Adapter
{
    const TYPE = 'fake';

    /**
     * @var FakeClientFactory
     */
    private $fakeClientFactory;

    /**
     * @param FakeClientFactory $fakeClientFactory
     */
    public function __construct(FakeClientFactory $fakeClientFactory)
    {
        $this->fakeClientFactory = $fakeClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentClientFactory()
    {
        return $this->fakeClientFactory;
    }
}
