<?php

namespace AppTestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AppTestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $environment = $container->getParameter('kernel.environment');

        if ($environment != 'smoke_test') {
            $loader->load('in-memory/event-store.xml');
            $loader->load('in-memory/repositories.xml');
        }

        $loader->load('integration/authenticator.xml');
        $loader->load('integration/builder.xml');
        $loader->load('integration/github.xml');
        $loader->load('integration/bitbucket.xml');
        $loader->load('integration/code-repositories.xml');
        $loader->load('integration/logstream.xml');
        $loader->load('integration/pipe.xml');
        $loader->load('integration/runner.xml');
        $loader->load('integration/keen.xml');
        $loader->load('integration/logitio.xml');
        $loader->load('integration/notifications.xml');
        $loader->load('integration/web-hook.xml');
        $loader->load('integration/security.xml');
        $loader->load('integration/storage.xml');
        $loader->load('integration/quayio.xml');

        $loader->load('controllers.xml');
        $loader->load('queue.xml');
    }
}
