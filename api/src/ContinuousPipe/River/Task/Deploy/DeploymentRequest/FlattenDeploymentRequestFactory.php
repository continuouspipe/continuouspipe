<?php

namespace ContinuousPipe\River\Task\Deploy\DeploymentRequest;

use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\TargetEnvironmentFactory;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FlattenDeploymentRequestFactory implements DeploymentRequestFactory
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
     * @var string
     */
    private $riverHostname;
    /**
     * @var bool
     */
    private $useSsl;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param TargetEnvironmentFactory $targetEnvironmentFactory
     * @param string $riverHostname
     * @param bool $useSsl
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TargetEnvironmentFactory $targetEnvironmentFactory, string $riverHostname, bool $useSsl)
    {
        $this->urlGenerator = $urlGenerator;
        $this->targetEnvironmentFactory = $targetEnvironmentFactory;
        $this->riverHostname = $riverHostname;
        $this->useSsl = $useSsl;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $callbackUrl = 'http'.($this->useSsl ? 's' : '').'://'.$this->riverHostname.$this->urlGenerator->generate('pipe_notification_post', [
            'tideUuid' => $tide->getUuid(),
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        $bucketUuid = $tide->getTeam()->getBucketUuid();

        return new DeploymentRequest(
            $this->targetEnvironmentFactory->create($tide, $configuration),
            new DeploymentRequest\Specification(
                $configuration->getComponents()
            ),
            $bucketUuid,
            new DeploymentRequest\Notification(
                $callbackUrl,
                $taskDetails->getLogId()
            )
        );
    }
}
