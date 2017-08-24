<?php

namespace ContinuousPipe\River\Analytics\Keen\Asynchronous\Handler;

use ContinuousPipe\River\Analytics\Keen\Asynchronous\Command\AddEventCommand;
use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;

class AddEventHandler
{
    /**
     * @var KeenClient
     */
    private $client;

    public function __construct(KeenClient $client)
    {
        $this->client = $client;
    }

    public function handle(AddEventCommand $command)
    {
        $this->client->addEvent($command->getCollection(), $command->getEvent());
    }
}
