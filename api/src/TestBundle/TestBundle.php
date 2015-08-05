<?php

namespace TestBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TestBundle\DependencyInjection\CompilerPass\ReplaceAsynchronousHandlerWithRegularOnesPass;

class TestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReplaceAsynchronousHandlerWithRegularOnesPass());
    }
}
