<?php

namespace ContinuousPipe\River\Tests\Logging;

use LogStream\Log;

class InMemoryLogStore
{
    /**
     * @var Log[]
     */
    private $logs = [];

    /**
     * @var Log[][]
     */
    private $logsByParent = [];

    /**
     * @param Log $log
     * @param Log $parent
     */
    public function store(Log $log, Log $parent = null)
    {
        $this->logs[$log->getId()] = $log;

        if (null !== $parent) {
            if (!array_key_exists($parent->getId(), $this->logsByParent)) {
                $this->logsByParent[$parent->getId()] = [];
            }

            $this->logsByParent[$parent->getId()][] = $log;
        }
    }

    /**
     * @return Log[]
     */
    public function findAll()
    {
        return $this->logs;
    }

    /**
     * @param Log $parent
     *
     * @return Log[]
     */
    public function findAllByParent(Log $parent)
    {
        return $this->logsByParent[$parent->getId()];
    }

    /**
     * @param string $id
     * @return Log
     */
    public function findById($id)
    {
        return $this->logs[$id];
    }
}
