<?php

namespace ContinuousPipe\Security\Encryption;

interface Vault
{
    /**
     * Encrypt the given value for the given namespace.
     *
     * @param string $namespace
     * @param string $plainValue
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function encrypt(string $namespace, string $plainValue) : string;

    /**
     * Decrypt the given value for the namespace.
     *
     * @param string $namespace
     * @param string $encryptedValue
     *
     * @throws EncryptionException
     *
     * @return string
     */
    public function decrypt(string $namespace, string $encryptedValue) : string;
}
