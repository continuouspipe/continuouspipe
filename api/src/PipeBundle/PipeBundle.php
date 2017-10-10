<?php

namespace PipeBundle;

use PipeBundle\DependencyInjection\CompilerPass\DeploymentCommandHandlersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PipeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DeploymentCommandHandlersPass());
    }
}
