<?php

namespace WorkerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use TestBundle\DependencyInjection\CompilerPass\ReplaceAsynchronousHandlerWithRegularOnesPass;

class WorkerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        if ($container->getParameter('worker_debug')) {
            $container->addCompilerPass(new ReplaceAsynchronousHandlerWithRegularOnesPass());
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('handler.xml');
        $loader->load('adapter.xml');
        $loader->load('deployment.xml');
        $loader->load('kubernetes.xml');
        $loader->load('notification.xml');
        $loader->load('tolerance.xml');
        $loader->load('logging.xml');
    }
}
