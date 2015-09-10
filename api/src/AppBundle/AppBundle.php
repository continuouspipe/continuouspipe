<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\MessageBusCommonBeforeHandlingMiddleware;
use AppBundle\DependencyInjection\Compiler\TaskFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TaskFactoryPass());
        $container->addCompilerPass(new MessageBusCommonBeforeHandlingMiddleware());
    }
}
