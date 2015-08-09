<?php

namespace AppBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AdapterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $adapterRegistry = $container->getDefinition('pipe.adapter_registry');
        foreach ($container->findTaggedServiceIds('pipe.adapter') as $id => $attributes) {
            $adapterRegistry->addMethodCall('register', [new Reference($id)]);
        }
    }
}
