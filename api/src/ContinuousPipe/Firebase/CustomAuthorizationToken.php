<?php

namespace ContinuousPipe\Firebase;

use Firebase\JWT\JWT;
use Firebase\ServiceAccount;

final class CustomAuthorizationToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTimeInterface
     */
    private $expirationDate;

    private function __construct(string $token, \DateTimeInterface $expirationDate)
    {
        $this->token = $token;
        $this->expirationDate = $expirationDate;
    }

    public static function create(ServiceAccount $serviceAccount, string $uid, array $claims, int $expiration) : self
    {
        $nowTimestamp = time();
        $expirationTimestamp = $nowTimestamp + $expiration;

        $payload = [
            'iss' => $serviceAccount->getClientEmail(),
            'sub' => $serviceAccount->getClientEmail(),
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $nowTimestamp,
            'exp' => $expirationTimestamp,
            'uid' => $uid,
            'claims' => $claims,
        ];

        $token = JWT::encode(
            $payload,
            $serviceAccount->getPrivateKey(),
            'RS256'
        );

        return new self(
            $token,
            \DateTime::createFromFormat('U', (string) $expirationTimestamp)
        );
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpirationDate(): \DateTimeInterface
    {
        return $this->expirationDate;
    }
}
