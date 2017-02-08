<?php

namespace ContinuousPipe\River\Flow\EncryptedVariable;

use Ramsey\Uuid\UuidInterface;

class PreviouslyKnownEncryptedVariableVault implements EncryptedVariableVault
{
    private $encryptionMapping = [];
    private $decryptionMapping = [];

    /**
     * {@inheritdoc}
     */
    public function encrypt(UuidInterface $flowUuid, string $plainValue): string
    {
        if (!isset($this->encryptionMapping[$flowUuid->toString()][$plainValue])) {
            throw new EncryptionException(sprintf(
                'No mapping known for the flow %s and plain value "%s"',
                $flowUuid->toString(),
                $plainValue
            ));
        }

        return $this->encryptionMapping[$flowUuid->toString()][$plainValue];
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(UuidInterface $flowUuid, string $encryptedValue): string
    {
        if (!isset($this->decryptionMapping[$flowUuid->toString()][$encryptedValue])) {
            throw new EncryptionException(sprintf(
                'No mapping known for the flow %s and encrypted value "%s"',
                $flowUuid->toString(),
                $encryptedValue
            ));
        }

        return $this->decryptionMapping[$flowUuid->toString()][$encryptedValue];
    }

    public function addEncryptionMapping(UuidInterface $flowUuid, string $plainValue, string $encryptedValue)
    {
        if (!array_key_exists($flowUuid->toString(), $this->encryptionMapping)) {
            $this->encryptionMapping[$flowUuid->toString()] = [];
        }

        $this->encryptionMapping[$flowUuid->toString()][$plainValue] = $encryptedValue;
    }

    public function addDecryptionMapping(UuidInterface $flowUuid, string $encryptedValue, string $plainValue)
    {
        if (!array_key_exists($flowUuid->toString(), $this->decryptionMapping)) {
            $this->decryptionMapping[$flowUuid->toString()] = [];
        }

        $this->decryptionMapping[$flowUuid->toString()][$encryptedValue] = $plainValue;
    }
}
