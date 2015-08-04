<?php

namespace WorkerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReplaceAsynchronousHandlerWithRegularOnesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('asynchronous_command_handler') as $id => $attributes) {
            $definition = $container->getDefinition($id);

            $definition->clearTag('asynchronous_command_handler');
            $definition->addTag('command_handler', $attributes[0]);
        }
    }
}
