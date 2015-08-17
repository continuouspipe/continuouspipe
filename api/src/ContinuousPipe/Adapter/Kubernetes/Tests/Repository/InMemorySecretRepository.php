<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Repository\SecretRepository;

class InMemorySecretRepository implements SecretRepository
{
    /**
     * @var Secret[]
     */
    private $secrets = [];

    /**
     * {@inheritdoc}
     */
    public function create(Secret $secret)
    {
        $this->secrets[] = $secret;

        return $secret;
    }
}
