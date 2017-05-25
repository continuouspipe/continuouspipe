<?php

namespace ContinuousPipe\River\Tide\Summary;

class Environment
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $cluster;

    public function __construct(string $name, string $cluster)
    {
        $this->name = $name;
        $this->cluster = $cluster;
    }
}
