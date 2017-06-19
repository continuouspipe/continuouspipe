<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Database\Reference;
use Firebase\Exception\ApiException;

class DatabaseFirebaseClient implements FirebaseClient
{
    /**
     * @var DatabaseFactory
     */
    private $databaseFactory;

    /**
     * @param DatabaseFactory $databaseFactory
     */
    public function __construct(DatabaseFactory $databaseFactory)
    {
        $this->databaseFactory = $databaseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $databaseUri, string $path, array $value)
    {
        return $this->databaseFactory->create($databaseUri)->getReference($path)->set($value);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $databaseUri, string $path, array $value)
    {
        return $this->databaseFactory->create($databaseUri)->getReference($path)->update($value);
    }

    public function remove(string $databaseUri, string $path)
    {
        return $this->databaseFactory->create($databaseUri)->getReference($path)->remove();
    }
}
