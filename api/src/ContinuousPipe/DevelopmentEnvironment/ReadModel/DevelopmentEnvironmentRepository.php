<?php

namespace ContinuousPipe\DevelopmentEnvironment\ReadModel;

use Ramsey\Uuid\UuidInterface;

interface DevelopmentEnvironmentRepository
{
    public function findByFlow(UuidInterface $flowUuid): array;

    public function save(DevelopmentEnvironment $developmentEnvironment);

    /**
     * @param UuidInterface $uuid
     *
     * @throws DevelopmentEnvironmentNotFound
     *
     * @return DevelopmentEnvironment
     */
    public function find(UuidInterface $uuid) : DevelopmentEnvironment;
}
