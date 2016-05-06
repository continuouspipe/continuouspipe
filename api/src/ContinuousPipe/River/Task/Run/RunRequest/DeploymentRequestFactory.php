<?php

namespace ContinuousPipe\River\Task\Run\RunRequest;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Task\Run\RunContext;
use ContinuousPipe\River\Task\Run\RunTaskConfiguration;
use ContinuousPipe\River\TideContext;
use ContinuousPipe\River\View\TideRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeploymentRequestFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TargetEnvironmentFactory
     */
    private $targetEnvironmentFactory;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param UrlGeneratorInterface    $urlGenerator
     * @param TargetEnvironmentFactory $targetEnvironmentFactory
     * @param TideRepository           $tideRepository
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TargetEnvironmentFactory $targetEnvironmentFactory, TideRepository $tideRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->targetEnvironmentFactory = $targetEnvironmentFactory;
        $this->tideRepository = $tideRepository;
    }

    /**
     * Create a deployment request for the following run configuration.
     *
     * @param RunContext           $context
     * @param RunTaskConfiguration $configuration
     *
     * @return DeploymentRequest
     */
    public function createDeploymentRequest(RunContext $context, RunTaskConfiguration $configuration)
    {
        $tide = $this->tideRepository->find($context->getTideUuid());

        return new DeploymentRequest(
            $this->targetEnvironmentFactory->create($tide, $configuration),
            new DeploymentRequest\Specification([
                $this->createComponent(
                    $this->createComponentName($context),
                    $configuration
                ),
            ]),
            new DeploymentRequest\Notification(
                $this->getNotificationUrl($context),
                $context->getTaskLog()->getId()
            ),
            $context->getTeam()->getBucketUuid()
        );
    }

    /**
     * Get the notification URL to give to the runner client.
     *
     * @param TideContext $context
     *
     * @return string
     */
    private function getNotificationUrl(TideContext $context)
    {
        return $this->urlGenerator->generate('runner_notification_post', [
            'tideUuid' => $context->getTideUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string               $name
     * @param RunTaskConfiguration $configuration
     *
     * @return Component
     */
    private function createComponent($name, RunTaskConfiguration $configuration)
    {
        return new Component(
            $name,
            $name,
            new Component\Specification(
                new Component\Source($configuration->getImage()),
                new Component\Accessibility(false, false),
                new Component\Scalability(false, 1),
                [],
                $this->createEnvironmentVariables($configuration),
                [],
                [],
                $this->getCommand($configuration)
            ),
            [],
            [],
            null,
            new Component\DeploymentStrategy(null, null, true, false)
        );
    }

    /**
     * @param RunTaskConfiguration $configuration
     *
     * @return Component\EnvironmentVariable[]
     */
    private function createEnvironmentVariables(RunTaskConfiguration $configuration)
    {
        $variables = [];

        foreach ($configuration->getEnvironmentVariables() as $key => $value) {
            $variables[] = new Component\EnvironmentVariable($key, $value);
        }

        return $variables;
    }

    /**
     * @param RunTaskConfiguration $configuration
     *
     * @return array
     */
    private function getCommand(RunTaskConfiguration $configuration)
    {
        return ['sh', '-cex', implode('; ', $configuration->getCommands())];
    }

    /**
     * @param RunContext $context
     *
     * @return string
     */
    private function createComponentName(RunContext $context)
    {
        return (new Slugify())->slugify($context->getTaskId());
    }
}
