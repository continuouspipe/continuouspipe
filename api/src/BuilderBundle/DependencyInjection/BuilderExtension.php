<?php

namespace BuilderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class BuilderExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('aggregates/build.xml');
        $loader->load('aggregates/build-step.xml');
        $loader->load('build-adapters/docker.xml');
        $loader->load('build-adapters/google-cloud.xml');
        $loader->load('builder.xml');
        $loader->load('credentials.xml');
        $loader->load('logging.xml');
        $loader->load('notification.xml');
    }
}
