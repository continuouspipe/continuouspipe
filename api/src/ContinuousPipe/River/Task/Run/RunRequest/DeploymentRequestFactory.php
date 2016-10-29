<?php

namespace ContinuousPipe\River\Task\Run\RunRequest;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Pipe\DeploymentRequestEnhancer\DeploymentRequestEnhancer;
use ContinuousPipe\River\Task\Run\RunTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\View\Tide;
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
     * @var DeploymentRequestEnhancer
     */
    private $deploymentRequestEnhancer;

    /**
     * @param UrlGeneratorInterface     $urlGenerator
     * @param TargetEnvironmentFactory  $targetEnvironmentFactory
     * @param DeploymentRequestEnhancer $deploymentRequestEnhancer
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TargetEnvironmentFactory $targetEnvironmentFactory, DeploymentRequestEnhancer $deploymentRequestEnhancer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->targetEnvironmentFactory = $targetEnvironmentFactory;
        $this->deploymentRequestEnhancer = $deploymentRequestEnhancer;
    }

    /**
     * Create a deployment request for the following run configuration.
     *
     * @param Tide                 $tide
     * @param TaskDetails          $taskDetails
     * @param RunTaskConfiguration $configuration
     *
     * @return DeploymentRequest
     */
    public function createDeploymentRequest(Tide $tide, TaskDetails $taskDetails, RunTaskConfiguration $configuration)
    {
        $request = new DeploymentRequest(
            $this->targetEnvironmentFactory->create($tide, $configuration),
            new DeploymentRequest\Specification([
                $this->createComponent(
                    $this->createComponentName($taskDetails),
                    $configuration
                ),
            ]),
            new DeploymentRequest\Notification(
                $this->getNotificationUrl($tide),
                $taskDetails->getLogId()
            ),
            $tide->getTeam()->getBucketUuid()
        );

        return $this->deploymentRequestEnhancer->enhance(
            $tide,
            $request
        );
    }

    /**
     * Get the notification URL to give to the runner client.
     *
     * @param Tide $tide
     *
     * @return string
     */
    private function getNotificationUrl(Tide $tide)
    {
        return $this->urlGenerator->generate('runner_notification_post', [
            'tideUuid' => $tide->getUuid(),
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
     * @param TaskDetails $taskDetails
     *
     * @return string
     */
    private function createComponentName(TaskDetails $taskDetails)
    {
        return (new Slugify())->slugify($taskDetails->getIdentifier());
    }
}
