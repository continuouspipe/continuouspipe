<?php

namespace AppBundle;

use AppBundle\DependencyInjection\CompilerPass\AdapterCompilerPass;
use AppBundle\DependencyInjection\CompilerPass\DeploymentCommandHandlersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AdapterCompilerPass());
        $container->addCompilerPass(new DeploymentCommandHandlersPass());
    }
}
