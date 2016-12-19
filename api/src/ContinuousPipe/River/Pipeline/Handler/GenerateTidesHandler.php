<?php

namespace ContinuousPipe\River\Pipeline\Handler;

use ContinuousPipe\River\Pipeline\Command\GenerateTides;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use SimpleBus\Message\Bus\MessageBus;

class GenerateTidesHandler
{
    /**
     * @var PipelineTideGenerator
     */
    private $tideGenerator;
    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(PipelineTideGenerator $tideGenerator, MessageBus $eventBus)
    {
        $this->tideGenerator = $tideGenerator;
        $this->eventBus = $eventBus;
    }

    /**
     * @param GenerateTides $command
     */
    public function handle(GenerateTides $command)
    {
        $tides = $this->tideGenerator->generate($command->getRequest());

        if (empty($tides)) {
            var_dump('WTF?');
        }

        foreach ($tides as $tide) {
            foreach ($tide->popNewEvents() as $event) {
                $this->eventBus->handle($event);
            }
        }
    }
}
