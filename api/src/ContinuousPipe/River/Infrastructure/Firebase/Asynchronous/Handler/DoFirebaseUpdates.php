<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Asynchronous\Handler;

use ContinuousPipe\River\Infrastructure\Firebase\Asynchronous\Command\UpdateFirebaseCommand;
use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use Firebase\Exception\FirebaseException;
use Psr\Log\LoggerInterface;

class DoFirebaseUpdates
{
    /**
     * @var FirebaseClient
     */
    private $firebaseClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(FirebaseClient $firebaseClient, LoggerInterface $logger)
    {
        $this->firebaseClient = $firebaseClient;
        $this->logger = $logger;
    }

    /**
     * @param UpdateFirebaseCommand $command
     */
    public function handle(UpdateFirebaseCommand $command)
    {
        $method = $this->getMethod($command);

        try {
            $this->firebaseClient->$method($command->getDatabaseUri(), $command->getPath(), $command->getValue());
        } catch (FirebaseException $e) {
            $this->logger->warning('Unable to update firebase', [
                'command' => $command->getCommand(),
                'path' => $command->getPath(),
                'exception' => $e,
            ]);
        }
    }

    private function getMethod(UpdateFirebaseCommand $command) : string
    {
        return $command->getCommand();
    }
}
