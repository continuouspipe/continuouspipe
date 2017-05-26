<?php

namespace ContinuousPipe\River\Tide\Summary;

class Environment
{
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string
     */
    private $cluster;

    public function __construct(string $identifier, string $cluster)
    {
        $this->identifier = $identifier;
        $this->cluster = $cluster;
    }
}
