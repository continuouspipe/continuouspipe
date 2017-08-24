<?php

namespace ContinuousPipe\River\Analytics\Logitio\Asynchronous\Handler;

use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;
use ContinuousPipe\River\Analytics\Logitio\Asynchronous\Command\AddEventCommand;

class AddEventHandler
{
    /**
     * @var LogitioClient
     */
    private $client;

    public function __construct(LogitioClient $client)
    {
        $this->client = $client;
    }

    public function handle(AddEventCommand $command)
    {
        $this->client->addEvent($command->getLogType(), $command->getEvent());
    }
}
