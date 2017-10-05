<?php

namespace ContinuousPipe\Pipe\Kubernetes\Tests\Repository;

use Kubernetes\Client\Exception\SecretNotFound;
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
        $this->secrets[$secret->getMetadata()->getName()] = $secret;

        return $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        if (!array_key_exists($name, $this->secrets)) {
            throw new SecretNotFound();
        }

        return $this->secrets[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->secrets);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Secret $secret)
    {
        $this->secrets[$secret->getMetadata()->getName()] = $secret;

        return $secret;
    }
}
