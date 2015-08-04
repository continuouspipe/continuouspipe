<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Builder\Client\BuilderClient;
use ContinuousPipe\Builder\HttpNotification;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\Command\BuildImageCommand;
use ContinuousPipe\River\Event\Build\ImageBuildStarted;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use LogStream\LoggerFactory;
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param BuilderClient $builderClient
     * @param TideRepository $tideRepository
     * @param MessageBus $eventBus
     * @param UrlGeneratorInterface $urlGenerator
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(BuilderClient $builderClient, TideRepository $tideRepository, MessageBus $eventBus, UrlGeneratorInterface $urlGenerator, LoggerFactory $loggerFactory)
    {
        $this->builderClient = $builderClient;
        $this->eventBus = $eventBus;
        $this->tideRepository = $tideRepository;
        $this->urlGenerator = $urlGenerator;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param BuildImageCommand $command
     */
    public function handle(BuildImageCommand $command)
    {
        $logger = $this->loggerFactory->from($command->getLog());
        $logger->start();

        $tideUuid = $command->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);

        $buildRequest = $this->getBuildRequestWithNotificationConfiguration($tide, $command->getBuildRequest());
        $build = $this->builderClient->build($buildRequest, $tide->getUser());

        $this->eventBus->handle(new ImageBuildStarted($tideUuid, $build));
    }

    /**
     * Add the notification configuration to the created build request.
     *
     * @param Tide         $tide
     * @param BuildRequest $buildRequest
     *
     * @return BuildRequest
     */
    private function getBuildRequestWithNotificationConfiguration(Tide $tide, BuildRequest $buildRequest)
    {
        $address = $this->urlGenerator->generate('builder_notification_post', [
            'tideUuid' => (string) $tide->getUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $httpNotification = HttpNotification::fromAddress($address);

        $buildRequest = new BuildRequest($buildRequest->getRepository(), $buildRequest->getImage(), Notification::withHttp($httpNotification));

        return $buildRequest;
    }
}
