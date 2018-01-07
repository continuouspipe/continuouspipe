<?php

namespace ContinuousPipe\River\Analytics\Logitio\Asynchronous\Client;

use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;
use ContinuousPipe\River\Analytics\Logitio\Asynchronous\Command\AddEventCommand;
use SimpleBus\Message\Bus\MessageBus;

class TransformCallsToCommand implements LogitioClient
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
    public function addEvent($logType, array $event)
    {
        $this->commandBus->handle(new AddEventCommand($logType, $event));
    }
}
