<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository\Trace;

use Kubernetes\Client\Model\Secret;
use Kubernetes\Client\Repository\SecretRepository;

class TraceableSecretRepository implements SecretRepository
{
    /**
     * @var Secret[]
     */
    private $created = [];

    /**
     * @var SecretRepository
     */
    private $repository;

    /**
     * @param SecretRepository $repository
     */
    public function __construct(SecretRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Secret $secret)
    {
        $this->repository->create($secret);

        $this->created[] = $secret;

        return $secret;
    }

    /**
     * @return \Kubernetes\Client\Model\Secret[]
     */
    public function getCreated()
    {
        return $this->created;
    }
}
