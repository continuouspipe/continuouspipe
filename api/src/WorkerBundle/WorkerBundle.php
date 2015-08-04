<?php

namespace WorkerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WorkerBundle\DependencyInjection\CompilerPass\ReplaceAsynchronousHandlerWithRegularOnesPass;

class WorkerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReplaceAsynchronousHandlerWithRegularOnesPass());
    }
}
