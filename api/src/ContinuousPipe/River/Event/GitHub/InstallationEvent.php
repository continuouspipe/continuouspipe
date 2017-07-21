<?php

namespace ContinuousPipe\River\Event\GitHub;

use GitHub\Integration\Installation;

abstract class InstallationEvent
{
    /**
     * @var Installation
     */
    private $installation;

    public function __construct(Installation $installation)
    {
        $this->installation = $installation;
    }

    public function getInstallation(): Installation
    {
        return $this->installation;
    }
}
