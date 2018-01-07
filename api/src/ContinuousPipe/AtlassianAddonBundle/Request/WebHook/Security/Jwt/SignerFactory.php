<?php

namespace ContinuousPipe\AtlassianAddonBundle\Request\WebHook\Security\Jwt;

use Lcobucci\JWT\Signer;

class SignerFactory
{
    public function create(string $algorithmId): Signer
    {
        switch ($algorithmId) {
            case 'HS256':
                return new Signer\Hmac\Sha256();
            case 'HS384':
                return new Signer\Hmac\Sha384();
            case 'HS512':
                return new Signer\Hmac\Sha512();
            case 'RS256':
                return new Signer\Rsa\Sha256();
            case 'RS384':
                return new Signer\Rsa\Sha384();
            case 'RS512':
                return new Signer\Rsa\Sha512();
            case 'ES256':
                return new Signer\Ecdsa\Sha256();
            case 'ES384':
                return new Signer\Ecdsa\Sha384();
            case 'ES512':
                return new Signer\Ecdsa\Sha512();
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported algorithm given "%s".', $algorithmId));
        }
    }
}
