<?php

namespace AppTestBundle\DependencyInjection\CompilerPass;

use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReplaceAuthenticatorCache implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('security.authenticator.cache')->setClass(ArrayCache::class);
    }
}
