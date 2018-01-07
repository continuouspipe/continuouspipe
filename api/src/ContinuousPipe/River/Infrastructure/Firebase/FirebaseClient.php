<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Exception\ApiException;

interface FirebaseClient
{
    /**
     * @param string $databaseUri
     * @param string $path
     * @param array $value
     *
     * @throws ApiException
     */
    public function set(string $databaseUri, string $path, array $value);

    /**
     * @param string $databaseUri
     * @param string $path
     * @param array $value
     *
     * @throws ApiException
     */
    public function update(string $databaseUri, string $path, array $value);

    /**
     * @param string $databaseUri
     * @param string $path
     *
     * @throws ApiException
     */
    public function remove(string $databaseUri, string $path);
}
