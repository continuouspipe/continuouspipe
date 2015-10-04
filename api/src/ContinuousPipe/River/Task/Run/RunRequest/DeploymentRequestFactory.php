<?php

namespace ContinuousPipe\River\Task\Run\RunRequest;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Task\Deploy\Naming\EnvironmentNamingStrategy;
use ContinuousPipe\River\Task\Run\RunContext;
use ContinuousPipe\River\Task\Run\RunTaskConfiguration;
use ContinuousPipe\River\TideContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeploymentRequestFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EnvironmentNamingStrategy
     */
    private $environmentNamingStrategy;

    /**
     * @param UrlGeneratorInterface     $urlGenerator
     * @param EnvironmentNamingStrategy $environmentNamingStrategy
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EnvironmentNamingStrategy $environmentNamingStrategy)
    {
        $this->urlGenerator = $urlGenerator;
        $this->environmentNamingStrategy = $environmentNamingStrategy;
    }

    /**
     * Create a deployment request for the following run configuration.
     *
     * @param RunContext $context
     *
     * @return DeploymentRequest
     */
    public function createDeploymentRequest(RunContext $context, RunTaskConfiguration $configuration)
    {
        return new DeploymentRequest(
            new DeploymentRequest\Target(
                $this->getEnvironmentName($context),
                $configuration->getProviderName()
            ),
            new DeploymentRequest\Specification([
                $this->createComponent($context->getTaskId(), $configuration),
            ]),
            new DeploymentRequest\Notification(
                $this->getNotificationUrl($context),
                $context->getTaskLog()->getId()
            )
        );
    }

    /**
     * @param TideContext $context
     *
     * @return string
     */
    private function getEnvironmentName(TideContext $context)
    {
        return $this->environmentNamingStrategy->getName(
            $context->getFlowUuid(),
            $context->getCodeReference()
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
            false,
            null,
            new Component\DeploymentStrategy(true)
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

        foreach ($configuration->getEnvironment() as $key => $value) {
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
        return ['sh', '-c', implode(' ', $configuration->getCommands())];
    }
}
