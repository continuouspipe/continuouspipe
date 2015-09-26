<?php

namespace AppTestBundle\DependencyInjection\CompilerPass;

use ContinuousPipe\River\Tests\MessageBus\SerializationHandlerDecorator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

            $decoratorId = $id.'.decorated';
            $decorator = new Definition(SerializationHandlerDecorator::class, [
                new Reference('simple_bus.jms_serializer.object_serializer'),
                new Reference($decoratorId.'.inner'),
            ]);

            $decorator->setDecoratedService($id);

            $container->addDefinitions([
                $decoratorId => $decorator,
            ]);
        }
    }
}
