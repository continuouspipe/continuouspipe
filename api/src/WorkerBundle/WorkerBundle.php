<?php

namespace WorkerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WorkerBundle\DependencyInjection\CompilerPass\DeploymentCommandHandlersPass;

class WorkerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DeploymentCommandHandlersPass());
    }
}
