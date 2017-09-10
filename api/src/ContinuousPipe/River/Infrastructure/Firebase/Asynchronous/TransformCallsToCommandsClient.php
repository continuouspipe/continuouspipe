<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Asynchronous;

use ContinuousPipe\River\Infrastructure\Firebase\Asynchronous\Command\UpdateFirebaseCommand;
use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use SimpleBus\Message\Bus\MessageBus;

class TransformCallsToCommandsClient implements FirebaseClient
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $databaseUri, string $path, array $value)
    {
        $this->commandBus->handle(new UpdateFirebaseCommand(
            UpdateFirebaseCommand::COMMAND_SET,
            $databaseUri,
            $path,
            $value
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $databaseUri, string $path, array $value)
    {
        $this->commandBus->handle(new UpdateFirebaseCommand(
            UpdateFirebaseCommand::COMMAND_UPDATE,
            $databaseUri,
            $path,
            $value
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $databaseUri, string $path)
    {
        $this->commandBus->handle(new UpdateFirebaseCommand(
            UpdateFirebaseCommand::COMMAND_REMOVE,
            $databaseUri,
            $path
        ));
    }
}
