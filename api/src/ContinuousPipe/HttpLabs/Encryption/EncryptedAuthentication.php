<?php

namespace ContinuousPipe\HttpLabs\Encryption;

use ContinuousPipe\HttpLabs\Authentication;
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

    public function encrypt(Authentication $authentication) : string
    {
        return $this->vault->encrypt(
            $this->namespace,
            \GuzzleHttp\json_encode([
                'api_key' => $authentication->getApiKey(),
            ])
        );
    }

    public function decrypt(string $encrypted) : Authentication
    {
        $decrypted = $this->vault->decrypt($this->namespace, $encrypted);

        try {
            $json = \GuzzleHttp\json_decode($decrypted, true);
        } catch (\InvalidArgumentException $e) {
            throw new EncryptionException('The decrypted string is not a valid JSON document');
        }

        return new Authentication($json['api_key']);
    }
}
