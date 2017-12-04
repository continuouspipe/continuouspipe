<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AuthenticatorBucketRepository implements BucketRepository
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @param AuthenticatorClient $authenticatorClient
     */
    public function __construct(AuthenticatorClient $authenticatorClient)
    {
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        return $this->authenticatorClient->findBucketByUuid($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Bucket $bucket)
    {
        if (method_exists($this->authenticatorClient, 'addBucket')) {
            return $this->authenticatorClient->addBucket($bucket);
        }

        throw new \RuntimeException('Unable to save bucket to authenticator API');
    }
}
