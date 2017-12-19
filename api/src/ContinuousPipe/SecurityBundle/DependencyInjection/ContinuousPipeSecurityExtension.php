<?php

namespace ContinuousPipe\SecurityBundle\DependencyInjection;

use ContinuousPipe\Security\Encryption\CachedVault;
use ContinuousPipe\Security\Encryption\GoogleKms\CachedKeyResolver;
use ContinuousPipe\Security\Encryption\GoogleKms\CreateOrReturnKeyName;
use ContinuousPipe\Security\Encryption\GoogleKms\GoogleKmsClientResolver;
use ContinuousPipe\Security\Encryption\GoogleKms\GoogleKmsVault;
use ContinuousPipe\Security\Encryption\PhpEncryption\PhpEncryptionVault;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ContinuousPipeSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('security.xml');

        foreach ($config['vaults'] as $name => $configuration) {
            $this->registerVault($container, $loader, $name, $configuration);
        }
    }

    private function registerVault(ContainerBuilder $container, LoaderInterface $loader, string $name, array $config)
    {
        $vaultName = 'security.vaults.'.$name;

        if (isset($config['google_kms'])) {
            $container->setDefinition($clientResolverId = $vaultName.'.client_resolver', new Definition(GoogleKmsClientResolver::class, [
                $config['google_kms']['service_account_path'],
            ]));

            $container->setDefinition($keyResolverId = $vaultName.'.key_resolver', new Definition(CreateOrReturnKeyName::class, [
                new Reference($clientResolverId),
                $config['google_kms']['project_id'],
                $config['google_kms']['location'],
                $config['google_kms']['key_ring']
            ]));

            $container->setDefinition($vaultName, new Definition(GoogleKmsVault::class, [
                new Reference($clientResolverId),
                new Reference($keyResolverId),
            ]));

            if (isset($config['google_kms']['key_cache_service'])) {
                $cachedKeyResolverId = $keyResolverId.'.cached';

                $cachedKeyResolverDefinition = new Definition(CachedKeyResolver::class, [
                    new Reference($cachedKeyResolverId.'.inner'),
                    new Reference($config['google_kms']['key_cache_service']),
                ]);
                $cachedKeyResolverDefinition->setDecoratedService($keyResolverId);

                $container->setDefinition($cachedKeyResolverId, $cachedKeyResolverDefinition);
            }
        } elseif (isset($config['php_encryption'])) {
            $container->setDefinition($vaultName, new Definition(PhpEncryptionVault::class, [
                $config['php_encryption']['key'],
            ]));
        } else {
            throw new \RuntimeException('At least the `google_kms` or `php_encryption` should be configured');
        }

        if (isset($config['cache_service'])) {
            $cachedVaultId = $vaultName.'.cached';
            $cachedVaultDefinition = new Definition(CachedVault::class, [
                new Reference($cachedVaultId.'.inner'),
                new Reference($config['cache_service']),
            ]);
            $cachedVaultDefinition->setDecoratedService($vaultName);

            $container->setDefinition($cachedVaultId, $cachedVaultDefinition);
        }
    }
}
