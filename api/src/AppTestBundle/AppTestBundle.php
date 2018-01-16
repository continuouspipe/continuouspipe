<?php

namespace AppTestBundle;

use AppTestBundle\DependencyInjection\CompilerPass\RemoveAutomaticTideStartCompilerPass;
use AppTestBundle\DependencyInjection\CompilerPass\RemoveTheServiceResetter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppTestBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RemoveAutomaticTideStartCompilerPass());
        $container->addCompilerPass(new RemoveTheServiceResetter());
    }
}
