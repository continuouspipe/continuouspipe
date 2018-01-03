<?php


namespace ContinuousPipe\River\Repository;

use Ramsey\Uuid\UuidInterface;

class KeepTideAggregateInMemory implements TideRepository
{
    private $decoratedRepository;
    private $memory = [];

    public function __construct(TideRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        if (!isset($this->memory[$uuid->toString()])) {
            $this->memory[$uuid->toString()] = $this->decoratedRepository->find($uuid);
        }

        return $this->memory[$uuid->toString()];
    }
}
