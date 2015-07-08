<?php

namespace Builder\Handler;

use Builder\Command\BuildCommand;
use Builder\DockerBuilder;

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
