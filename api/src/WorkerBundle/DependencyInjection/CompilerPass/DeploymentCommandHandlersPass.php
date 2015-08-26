<?php

namespace WorkerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DeploymentCommandHandlersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $deploymentCommandsHandler = $container->getDefinition('pipe.deployment.deployment_commands_handler');
        $handleCommands = [];

        foreach ($container->findTaggedServiceIds('deployment_command_handler') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $deploymentCommandsHandler->addMethodCall('register', [
                    $attribute['handles'],
                    new Reference($id),
                ]);

                $handleCommands[] = $attribute['handles'];
            }
        }

        foreach (array_unique($handleCommands) as $command) {
            $deploymentCommandsHandler->addTag('command_handler', ['handles' => $command]);
        }
    }
}
