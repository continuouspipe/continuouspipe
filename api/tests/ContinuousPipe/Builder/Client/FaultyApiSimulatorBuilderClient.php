<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;

class FaultyApiSimulatorBuilderClient implements BuilderClient
{
    /**
     * @var BuilderClient
     */
    private $decoratedBuilderClient;

    /**
     * @var array
     */
    private $faultGenerators = [];

    public function __construct(BuilderClient $decoratedBuilderClient)
    {
        $this->decoratedBuilderClient = $decoratedBuilderClient;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest, User $user) : Build
    {
        if ($this->needToGenerateFault()) {
            $this->generateFault();
        }

        return $this->decoratedBuilderClient->build($buildRequest, $user);
    }

    public function addFault(callable $faultGenerator)
    {
        $this->faultGenerators[] = $faultGenerator;
    }

    private function needToGenerateFault()
    {
        return 0 < count($this->faultGenerators);
    }

    private function generateFault()
    {
        $generator = array_shift($this->faultGenerators);

        $generator();
    }
}
