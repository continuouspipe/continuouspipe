<?php

namespace ContinuousPipe\River\Flow\EncryptedVariable;

use ContinuousPipe\Security\Encryption\EncryptionException as SecurityEncryptionException;
use ContinuousPipe\Security\Encryption\Vault;
use Ramsey\Uuid\UuidInterface;

class SecurityVaultEncryptedVariableVault implements EncryptedVariableVault
{
    /**
     * @var Vault
     */
    private $vault;

    /**
     * @param Vault $vault
     */
    public function __construct(Vault $vault)
    {
        $this->vault = $vault;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(UuidInterface $flowUuid, string $plainValue): string
    {
        try {
            return $this->vault->encrypt($this->key($flowUuid), $plainValue);
        } catch (SecurityEncryptionException $e) {
            throw new EncryptionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(UuidInterface $flowUuid, string $encryptedValue): string
    {
        try {
            return $this->vault->decrypt($this->key($flowUuid), $encryptedValue);
        } catch (SecurityEncryptionException $e) {
            throw new EncryptionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function key(UuidInterface $flowUuid) : string
    {
        return 'flow-'.$flowUuid->toString();
    }
}
