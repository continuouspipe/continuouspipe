<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TaskPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->getDefinition('river.task_registry');
        foreach ($container->findTaggedServiceIds('river.task') as $id => $attributes) {
            $registryDefinition->addMethodCall('register', [
                $attributes[0]['task'],
                $id,
            ]);
        }
    }
}
