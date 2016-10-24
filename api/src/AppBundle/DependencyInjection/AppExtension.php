<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.xml');
        $loader->load('github.xml');
        $loader->load('docker-compose.xml');
        $loader->load('builder.xml');
        $loader->load('pipe.xml');
        $loader->load('flow.xml');
        $loader->load('river.xml');
        $loader->load('tide.xml');
        $loader->load('events.xml');
        $loader->load('tasks.xml');
        $loader->load('notifications.xml');
        $loader->load('queue.xml');
        $loader->load('security.xml');
        $loader->load('logstream.xml');
        $loader->load('web-hook.xml');
        $loader->load('build/task.xml');
        $loader->load('build/handlers.xml');
        $loader->load('build/logging.xml');
        $loader->load('deploy/task.xml');
        $loader->load('deploy/logging.xml');
        $loader->load('run/task.xml');
        $loader->load('run/handlers.xml');
        $loader->load('run/logging.xml');
        $loader->load('wait/task.xml');
        $loader->load('wait/logging.xml');
        $loader->load('analytics/keen.xml');
        $loader->load('recover/timed-out-tides.xml');
        $loader->load('recover/cancel.xml');
    }
}
