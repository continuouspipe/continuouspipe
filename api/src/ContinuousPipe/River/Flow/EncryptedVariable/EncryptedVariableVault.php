<?php

namespace ContinuousPipe\River\Flow\EncryptedVariable;

use Ramsey\Uuid\UuidInterface;

interface EncryptedVariableVault
{
    /**
     * Encrypt the given value for the given flow.
     *
     * @param UuidInterface $flowUuid
     * @param string $plainValue
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function encrypt(UuidInterface $flowUuid, string $plainValue) : string;

    /**
     * Decrypt the given value for the given flow.
     *
     * @param UuidInterface $flowUuid
     * @param string $encryptedValue
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function decrypt(UuidInterface $flowUuid, string $encryptedValue) : string;
}
