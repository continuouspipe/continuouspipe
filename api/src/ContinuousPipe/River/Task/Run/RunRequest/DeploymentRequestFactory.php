<?php

namespace ContinuousPipe\River\Task\Run\RunRequest;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Pipe\DeploymentRequestEnhancer\DeploymentRequestEnhancer;
use ContinuousPipe\River\Task\Run\RunTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeploymentRequestFactory
{
    /**
     * @var TargetEnvironmentFactory
     */
    private $targetEnvironmentFactory;

    /**
     * @var DeploymentRequestEnhancer
     */
    private $deploymentRequestEnhancer;

    public function __construct(
        TargetEnvironmentFactory $targetEnvironmentFactory,
        DeploymentRequestEnhancer $deploymentRequestEnhancer
    ) {
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
     * @throws DeploymentRequestException
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
            $tide->getTeam()->getBucketUuid(),
            new DeploymentRequest\Notification(
                null,
                $taskDetails->getLogId()
            ),
            [
                'tide_uuid' => $tide->getUuid()->toString(),
                'task' => 'run',
            ]
        );

        return $this->deploymentRequestEnhancer->enhance(
            $tide,
            $taskDetails,
            $request
        );
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
                $configuration->getVolumes(),
                $configuration->getVolumeMounts(),
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
        return (new Slugify())->slugify('run-'.$taskDetails->getIdentifier());
    }
}
