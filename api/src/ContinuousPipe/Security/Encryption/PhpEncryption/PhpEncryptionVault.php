<?php

namespace ContinuousPipe\Security\Encryption\PhpEncryption;

use ContinuousPipe\Security\Encryption\EncryptionException;
use ContinuousPipe\Security\Encryption\Vault;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Key;

class PhpEncryptionVault implements Vault
{
    /**
     * @var Key
     */
    private $key;

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = Key::loadFromAsciiSafeString($key);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $namespace, string $plainValue): string
    {
        try {
            return Crypto::encrypt($namespace.':'.$plainValue, $this->key);
        } catch (CryptoException $e) {
            throw new EncryptionException('Unable to encrypt the value successfully', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $namespace, string $encryptedValue): string
    {
        try {
            $decryptedValue = Crypto::decrypt($encryptedValue, $this->key);
        } catch (CryptoException $e) {
            throw new EncryptionException('Unable to decrypt the value successfully', $e->getCode(), $e);
        }

        if (substr($decryptedValue, 0, strlen($namespace) + 1) !== $namespace.':') {
            throw new EncryptionException('A different namespace disallow the decryption of the value');
        }

        return substr($decryptedValue, strlen($namespace) + 1);
    }
}
