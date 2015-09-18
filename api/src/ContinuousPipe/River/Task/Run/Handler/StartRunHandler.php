<?php

namespace ContinuousPipe\River\Task\Run\Handler;

use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\RunContext;
use ContinuousPipe\River\Task\Run\DockerCompose;
use ContinuousPipe\Runner\Client;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StartRunHandler
{
    /**
     * @var Client
     */
    private $runnerClient;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var DockerCompose\Reader
     */
    private $dockerComposeReader;

    /**
     * @param Client                $runnerClient
     * @param UrlGeneratorInterface $urlGenerator
     * @param MessageBus            $eventBus
     */
    public function __construct(
        Client $runnerClient,
        UrlGeneratorInterface $urlGenerator,
        DockerCompose\Reader $dockerComposeReader,
        MessageBus $eventBus)
    {
        $this->runnerClient = $runnerClient;
        $this->urlGenerator = $urlGenerator;
        $this->dockerComposeReader = $dockerComposeReader;
        $this->eventBus = $eventBus;
    }

    /**
     * @param StartRunCommand $command
     */
    public function handle(StartRunCommand $command)
    {
        $context = $command->getContext();
        $runUuid = $this->runnerClient->run(
            new Client\RunRequest(
                $this->getImage($context),
                [],
                $context->getCommands(),
                new Client\Logging(new Client\Logging\LogStream(
                    $context->getRunnerLog()->getId()
                )),
                new Client\Notification(new Client\Notification\Http(
                    $this->getNotificationUrl($context)
                ))
            ),
            $context->getUser()
        );

        $this->eventBus->handle(new RunStarted(
            $context->getTideUuid(),
            $runUuid,
            $command->getTaskId()
        ));
    }

    /**
     * @param RunContext $context
     *
     * @return string Docker image name
     */
    private function getImage(RunContext $context)
    {
        try {
            $imageName = $this->dockerComposeReader->getImageName($context);
            $tag = $context->getCodeReference()->getBranch();
        } catch (DockerCompose\ImageNameNotFound $e) {
            return $context->getServiceName();
        }

        return $imageName . ':' . $tag;
    }

    /**
     * Get the notification URL to give to the runner client.
     *
     * @param RunContext $context
     *
     * @return string
     */
    private function getNotificationUrl(RunContext $context)
    {
        return $this->urlGenerator->generate('runner_notification_post', [
            'tideUuid' => $context->getTideUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
