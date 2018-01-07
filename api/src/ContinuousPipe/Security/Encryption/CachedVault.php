<?php

namespace ContinuousPipe\Security\Encryption;

use Doctrine\Common\Cache\Cache;

/**
 * Provide a caching mechanism for vault.
 *
 * *Important note:* such vault is meant to be storing encrypted data, and be the only point of contact for this data.
 * Storing decrypted values in a persistent cache system would make those credentials leakable. Therefore, please only
 * use in-memory stores.
 */
class CachedVault implements Vault
{
    /**
     * @var Vault
     */
    private $decoratedVault;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $lifeTime;

    /**
     * @param Vault $decoratedVault
     * @param Cache $cache
     * @param int $lifeTime
     */
    public function __construct(Vault $decoratedVault, Cache $cache, int $lifeTime = 3600)
    {
        $this->decoratedVault = $decoratedVault;
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $namespace, string $plainValue): string
    {
        $cacheKey = 'vault:encrypted:'.md5($namespace).':'.md5($plainValue);
        if (false === ($decrypted = $this->cache->fetch($cacheKey))) {
            $decrypted = $this->decoratedVault->encrypt($namespace, $plainValue);

            $this->cache->save($cacheKey, $decrypted, $this->lifeTime);
        }

        return $decrypted;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $namespace, string $encryptedValue): string
    {
        $cacheKey = 'vault:decrypted:'.md5($namespace).':'.md5($encryptedValue);
        if (false === ($encrypted = $this->cache->fetch($cacheKey))) {
            $encrypted = $this->decoratedVault->decrypt($namespace, $encryptedValue);

            $this->cache->save($cacheKey, $encrypted, $this->lifeTime);
        }

        return $encrypted;
    }
}
