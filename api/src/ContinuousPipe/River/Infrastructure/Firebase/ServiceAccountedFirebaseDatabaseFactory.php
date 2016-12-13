<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Database;

final class ServiceAccountedFirebaseDatabaseFactory implements DatabaseFactory
{
    /**
     * @var string
     */
    private $serviceAccountPath;

    /**
     * @param string $serviceAccountPath
     */
    public function __construct(string $serviceAccountPath)
    {
        $this->serviceAccountPath = $serviceAccountPath;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $uri): Database
    {
        return \Firebase::fromServiceAccount($this->serviceAccountPath)->withDatabaseUri($uri)->getDatabase();
    }
}
