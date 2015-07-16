<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\River\Command\BuildImageCommand;

class BuildImageHandler
{
    /**
     * @var BuilderClient
     */
    private $builderClient;

    /**
     * @param BuilderClient $builderClient
     */
    public function __construct(BuilderClient $builderClient)
    {
        $this->builderClient = $builderClient;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        $this->builderClient->build($command->getBuildRequest());
    }
}
