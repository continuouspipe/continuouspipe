<?php

namespace ContinuousPipe\River\Pipeline\Handler;

use ContinuousPipe\River\Pipeline\Command\GenerateTides;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class GenerateTidesHandler
{
    private $tideGenerator;
    private $eventBus;
    private $logger;

    public function __construct(PipelineTideGenerator $tideGenerator, MessageBus $eventBus, LoggerInterface $logger)
    {
        $this->tideGenerator = $tideGenerator;
        $this->eventBus = $eventBus;
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

        foreach ($tides as $tide) {
            foreach ($tide->popNewEvents() as $event) {
                $this->eventBus->handle($event);
            }
        }
    }
}
