<?php

namespace TestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class TestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $environment = $container->getParameter('kernel.environment');

        if ($environment != 'integration') {
            $loader->load('integration/docker.xml');
            $loader->load('integration/google-cloud.xml');
        }

        if (!in_array($environment, ['test_elasticsearch', 'test_elasticsearch_secure'])) {
            $loader->load('reporting.xml');
        }

        $loader->load('integration/authenticator.xml');
        $loader->load('builder.xml');
        $loader->load('logging.xml');
        $loader->load('docker.xml');
    }
}
