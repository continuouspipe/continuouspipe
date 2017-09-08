<?php

namespace ContinuousPipe\Watcher;

class WatcherLog
{
    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $database
     * @param string $identifier
     */
    public function __construct(string $database, string $identifier)
    {
        $this->database = $database;
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
