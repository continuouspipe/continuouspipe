<?php

namespace ContinuousPipe\River\Analytics\Keen\Asynchronous\Client;

use ContinuousPipe\River\Analytics\Keen\Asynchronous\Command\AddEventCommand;
use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;
use SimpleBus\Message\Bus\MessageBus;

class TransformCallsToCommand implements KeenClient
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent($collection, array $event)
    {
        $this->commandBus->handle(new AddEventCommand($collection, $event));
    }
}
