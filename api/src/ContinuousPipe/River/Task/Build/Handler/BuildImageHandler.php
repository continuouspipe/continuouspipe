<?php

namespace ContinuousPipe\River\Task\Build\Handler;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Builder\HttpNotification;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\LogStreamLogging;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Build\Command\BuildImageCommand;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use LogStream\Log;
use LogStream\LoggerFactory;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BuildImageHandler
{
    /**
     * @var BuilderClient
     */
    private $builderClient;

    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param BuilderClient         $builderClient
     * @param TideRepository        $tideRepository
     * @param MessageBus            $eventBus
     * @param UrlGeneratorInterface $urlGenerator
     * @param LoggerFactory         $loggerFactory
     */
    public function __construct(BuilderClient $builderClient, TideRepository $tideRepository, MessageBus $eventBus, UrlGeneratorInterface $urlGenerator)
    {
        $this->builderClient = $builderClient;
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        $tideUuid = $command->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);

        $buildRequest = $this->getBuildRequestWithNotificationConfiguration($tideUuid, $command->getBuildRequest(), $command->getLog());
        $build = $this->builderClient->build($buildRequest, $tide->getContext()->getUser());

        $this->eventBus->handle(new BuildStarted($tideUuid, $build));
    }

    /**
     * Add the notification configuration to the created build request.
     *
     * @param Uuid         $tideUuid
     * @param BuildRequest $buildRequest
     * @param Log          $parentLog
     *
     * @return BuildRequest
     */
    private function getBuildRequestWithNotificationConfiguration(Uuid $tideUuid, BuildRequest $buildRequest, Log $parentLog)
    {
        $address = $this->urlGenerator->generate('builder_notification_post', [
            'tideUuid' => (string) $tideUuid,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $buildRequest = new BuildRequest(
            $buildRequest->getRepository(),
            $buildRequest->getImage(),
            Notification::withHttp(HttpNotification::fromAddress($address)),
            Logging::withLogStream(LogStreamLogging::fromParentLogIdentifier($parentLog->getId()))
        );

        return $buildRequest;
    }
}
