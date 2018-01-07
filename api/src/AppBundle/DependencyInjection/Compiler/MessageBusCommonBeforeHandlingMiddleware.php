<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessageBusCommonBeforeHandlingMiddleware implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->removeDefinition('simple_bus.command_bus.finishes_command_before_handling_next_middleware');
        $container->removeDefinition('simple_bus.event_bus.events.finishes_message_before_handling_next_middleware');
    }
}
