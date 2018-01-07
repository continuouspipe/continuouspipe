<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TaskFactoryPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->getDefinition('river.task_factory_registry');
        foreach ($container->findTaggedServiceIds('river.task_factory') as $id => $attributes) {
            $registryDefinition->addMethodCall('register', [
                $attributes[0]['task'],
                $id,
            ]);
        }
    }
}
