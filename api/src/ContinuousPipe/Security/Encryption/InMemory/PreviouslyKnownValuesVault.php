<?php

namespace ContinuousPipe\Security\Encryption\InMemory;

use ContinuousPipe\Security\Encryption\EncryptionException;
use ContinuousPipe\Security\Encryption\Vault;

class PreviouslyKnownValuesVault implements Vault
{
    private $encryptionMapping = [];
    private $decryptionMapping = [];

    /**
     * @var Vault|null
     */
    private $decoratedVault;

    /**
     * @param Vault|null $decoratedVault
     */
    public function __construct(Vault $decoratedVault = null)
    {
        $this->decoratedVault = $decoratedVault;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $namespace, string $plainValue): string
    {
        if (!isset($this->encryptionMapping[$namespace][$plainValue])) {
            if (null === $this->decoratedVault) {
                throw new EncryptionException(sprintf(
                    'No mapping known for the namespace %s and plain value "%s"',
                    $namespace,
                    $plainValue
                ));
            }

            return $this->decoratedVault->encrypt($namespace, $plainValue);
        }

        return $this->encryptionMapping[$namespace][$plainValue];
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $namespace, string $encryptedValue): string
    {
        if (!isset($this->decryptionMapping[$namespace][$encryptedValue])) {
            if (null === $this->decoratedVault) {
                throw new EncryptionException(sprintf(
                    'No mapping known for the namespace %s and encrypted value "%s"',
                    $namespace,
                    $encryptedValue
                ));
            }

            return $this->decoratedVault->decrypt($namespace, $encryptedValue);
        }

        return $this->decryptionMapping[$namespace][$encryptedValue];
    }

    public function addEncryptionMapping(string $namespace, string $plainValue, string $encryptedValue)
    {
        if (!array_key_exists($namespace, $this->encryptionMapping)) {
            $this->encryptionMapping[$namespace] = [];
        }

        $this->encryptionMapping[$namespace][$plainValue] = $encryptedValue;
    }

    public function addDecryptionMapping(string $namespace, string $encryptedValue, string $plainValue)
    {
        if (!array_key_exists($namespace, $this->decryptionMapping)) {
            $this->decryptionMapping[$namespace] = [];
        }

        $this->decryptionMapping[$namespace][$encryptedValue] = $plainValue;
    }
}
