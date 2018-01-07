<?php

namespace ContinuousPipe\DevelopmentEnvironment\ReadModel;

use Ramsey\Uuid\UuidInterface;

interface DevelopmentEnvironmentRepository
{
    /**
     * @param UuidInterface $flowUuid
     *
     * @return DevelopmentEnvironment[]
     */
    public function findByFlow(UuidInterface $flowUuid): array;

    /**
     * @param DevelopmentEnvironment $developmentEnvironment
     */
    public function save(DevelopmentEnvironment $developmentEnvironment);

    /**
     * @param UuidInterface $uuid
     *
     * @throws DevelopmentEnvironmentNotFound
     *
     * @return DevelopmentEnvironment
     */
    public function find(UuidInterface $uuid) : DevelopmentEnvironment;

    /**
     * @param UuidInterface $uuid
     *
     * @throws DevelopmentEnvironmentNotFound
     */
    public function delete(UuidInterface $uuid);
}
