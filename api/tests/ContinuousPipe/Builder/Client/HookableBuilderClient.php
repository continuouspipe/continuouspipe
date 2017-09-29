<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;

class HookableBuilderClient implements BuilderClient
{
    /**
     * @var BuilderClient
     */
    private $decoratedClient;

    /**
     * @var callable[]
     */
    private $hooks = [];

    /**
     * @param BuilderClient $decoratedClient
     */
    public function __construct(BuilderClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest, User $user) : Build
    {
        $build = $this->decoratedClient->build($buildRequest, $user);

        foreach ($this->hooks as $hook) {
            $build = $hook($build);
        }

        return $build;
    }

    public function addHook(callable $hook)
    {
        $this->hooks[] = $hook;
    }
}
