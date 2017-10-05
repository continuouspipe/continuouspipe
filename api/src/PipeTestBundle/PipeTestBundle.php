<?php

namespace PipeTestBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use PipeTestBundle\DependencyInjection\CompilerPass\ReplaceAsynchronousHandlerWithRegularOnesPass;

class PipeTestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReplaceAsynchronousHandlerWithRegularOnesPass());
    }
}
