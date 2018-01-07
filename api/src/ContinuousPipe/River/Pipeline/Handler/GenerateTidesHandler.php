<?php

namespace ContinuousPipe\River\Pipeline\Handler;

use ContinuousPipe\River\Pipeline\Command\GenerateTides;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use Psr\Log\LoggerInterface;

class GenerateTidesHandler
{
    private $tideGenerator;
    private $logger;

    public function __construct(PipelineTideGenerator $tideGenerator, LoggerInterface $logger)
    {
        $this->tideGenerator = $tideGenerator;
        $this->logger = $logger;
    }

    /**
     * @param GenerateTides $command
     */
    public function handle(GenerateTides $command)
    {
        $tides = $this->tideGenerator->generate($command->getRequest());

        if (empty($tides)) {
            $this->logger->debug('Generated no tide from the request', [
                'request' => $command->getRequest(),
                'flow_uuid' => $command->getRequest()->getFlow()->getUuid()->toString(),
            ]);
        }
    }
}
