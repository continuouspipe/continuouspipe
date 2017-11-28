<?php

namespace AuthenticatorTestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AuthenticatorTestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $environment = $container->getParameter('kernel.environment');
        $loader->load('in-memory/3rd-parties.xml');
        $loader->load('controllers.xml');
        $loader->load('traces.xml');
        $loader->load('http.xml');

        if ('smoke_test' !== $environment) {
            $loader->load('in-memory/repositories.xml');
        }
    }
}
