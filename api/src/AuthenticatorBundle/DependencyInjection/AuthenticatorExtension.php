<?php

namespace AuthenticatorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AuthenticatorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controllers.xml');
        $loader->load('admin.xml');
        $loader->load('billing.xml');
        $loader->load('alerts.xml');

        if ($container->getParameter('google_cloud_audit_enabled')) {
            $loader->load('audit-log.xml');
        }

        if ($container->getParameter('intercom_enabled')) {
            $loader->load('intercom.xml');
        }
    }
}
