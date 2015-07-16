<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\DockerBuilder;

class BuildHandler
{
    /**
     * @var DockerBuilder
     */
    private $builder;

    public function __construct(DockerBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(BuildCommand $command)
    {
        $this->builder->build($command->getBuild());
    }
}
