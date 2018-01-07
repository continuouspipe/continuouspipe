<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Database;

interface DatabaseFactory
{
    /**
     * Create the database object for the given URI.
     *
     * @param string $uri
     *
     * @return Database
     */
    public function create(string $uri) : Database;
}
