<?php

namespace ContinuousPipe\CloudFlare\Encryption;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;
use ContinuousPipe\Security\Encryption\EncryptionException;
use ContinuousPipe\Security\Encryption\Vault;

final class EncryptedAuthentication
{
    /**
     * @var Vault
     */
    private $vault;

    /**
     * @var EncryptionNamespace
     */
    private $namespace;

    public function __construct(Vault $vault, EncryptionNamespace $namespace)
    {
        $this->vault = $vault;
        $this->namespace = $namespace;
    }

    public function encrypt(CloudFlareAuthentication $authentication) : string
    {
        return $this->vault->encrypt(
            $this->namespace,
            \GuzzleHttp\json_encode([
                'api_key' => $authentication->getApiKey(),
                'email' => $authentication->getEmail(),
            ])
        );
    }

    public function decrypt(string $encrypted) : CloudFlareAuthentication
    {
        $decrypted = $this->vault->decrypt($this->namespace, $encrypted);

        try {
            $json = \GuzzleHttp\json_decode($decrypted, true);
        } catch (\InvalidArgumentException $e) {
            throw new EncryptionException('The decrypted string is not a valid JSON document');
        }

        return new CloudFlareAuthentication(
            $json['email'],
            $json['api_key']
        );
    }
}
