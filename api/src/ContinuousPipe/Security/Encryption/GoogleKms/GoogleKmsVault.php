<?php

namespace ContinuousPipe\Security\Encryption\GoogleKms;

use ContinuousPipe\Security\Encryption\EncryptionException;
use ContinuousPipe\Security\Encryption\Vault;

class GoogleKmsVault implements Vault
{
    /**
     * @var GoogleKmsClientResolver
     */
    private $clientResolver;

    /**
     * @var GoogleKmsKeyResolver
     */
    private $keyResolver;

    public function __construct(GoogleKmsClientResolver $clientResolver, GoogleKmsKeyResolver $keyResolver)
    {
        $this->clientResolver = $clientResolver;
        $this->keyResolver = $keyResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $namespace, string $plainValue): string
    {
        try {
            $encryptedResponse = $this->clientResolver->get()->projects_locations_keyRings_cryptoKeys->encrypt(
                $this->keyResolver->keyName($namespace),
                new \Google_Service_CloudKMS_EncryptRequest([
                    'plaintext' => base64_encode($plainValue),
                ])
            );
        } catch (\Google_Exception $e) {
            throw new EncryptionException('Unable to encrypt the value using flow\'s encryption key', $e->getCode(), $e);
        }

        return $encryptedResponse->ciphertext;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $namespace, string $encryptedValue): string
    {
        try {
            $decryptedResponse = $this->clientResolver->get()->projects_locations_keyRings_cryptoKeys->decrypt(
                $this->keyResolver->keyName($namespace),
                new \Google_Service_CloudKMS_DecryptRequest([
                    'ciphertext' => $encryptedValue,
                ])
            );
        } catch (\Google_Exception $e) {
            throw new EncryptionException('Unable to decrypt the value using flow\'s encryption key', $e->getCode(), $e);
        }

        return base64_decode($decryptedResponse->plaintext);
    }
}
