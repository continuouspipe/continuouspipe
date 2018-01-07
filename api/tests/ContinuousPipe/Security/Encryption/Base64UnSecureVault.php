<?php

namespace ContinuousPipe\Security\Encryption;

class Base64UnSecureVault implements Vault
{
    /**
     * {@inheritdoc}
     */
    public function encrypt(string $namespace, string $plainValue): string
    {
        return base64_encode($plainValue);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $namespace, string $encryptedValue): string
    {
        if (base64_encode(base64_decode($encryptedValue, true)) !== $encryptedValue) {
            throw new EncryptionException('base64 is not valid, cannot decrypt the value');
        }

        return base64_decode($encryptedValue);
    }
}
