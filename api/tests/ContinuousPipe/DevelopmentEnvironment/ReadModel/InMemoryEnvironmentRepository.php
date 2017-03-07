<?php

namespace ContinuousPipe\DevelopmentEnvironment\ReadModel;

use Ramsey\Uuid\UuidInterface;

class InMemoryEnvironmentRepository implements DevelopmentEnvironmentRepository
{
    private $environments = [];

    /**
     * {@inheritdoc}
     */
    public function findByFlow(UuidInterface $flowUuid): array
    {
        return array_values(array_filter($this->environments, function(DevelopmentEnvironment $environment) use ($flowUuid) {
            return $environment->getFlowUuid()->equals($flowUuid);
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function save(DevelopmentEnvironment $developmentEnvironment)
    {
        $this->environments[$developmentEnvironment->getUuid()->toString()] = $developmentEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid): DevelopmentEnvironment
    {
        if (!array_key_exists($uuid->toString(), $this->environments)) {
            throw new DevelopmentEnvironmentNotFound('The environment is not found');
        }

        return $this->environments[$uuid->toString()];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UuidInterface $uuid)
    {
        if (!array_key_exists($uuid->toString(), $this->environments)) {
            throw new DevelopmentEnvironmentNotFound('The environment is not found');
        }

        unset($this->environments[$uuid->toString()]);
    }
}
