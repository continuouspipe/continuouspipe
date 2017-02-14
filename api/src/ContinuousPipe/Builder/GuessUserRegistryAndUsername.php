<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestStep;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use ContinuousPipe\River\Task\Build\BuildTaskFactory;
use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use LogStream\Log;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class GuessUserRegistryAndUsername implements BuildRequestCreator
{
    /**
     * @var BuildRequestCreator
     */
    private $decoratedCreator;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        BuildRequestCreator $decoratedCreator,
        BucketRepository $bucketRepository,
        LoggerInterface $logger
    ) {
        $this->decoratedCreator = $decoratedCreator;
        $this->bucketRepository = $bucketRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuildRequests(
        UuidInterface $tideUuid,
        CodeReference $codeReference,
        BuildTaskConfiguration $configuration,
        UuidInterface $credentialsBucketUuid,
        Log $parentLog
    ): array
    {
        return $this->decoratedCreator->createBuildRequests(
            $tideUuid,
            $codeReference,
            new BuildTaskConfiguration(array_map(function(ServiceConfiguration $serviceConfiguration) use ($tideUuid, $credentialsBucketUuid) {
                return new ServiceConfiguration(array_map(function(BuildRequestStep $buildRequestStep) use ($tideUuid, $credentialsBucketUuid) {
                    if ($buildRequestStep->getImage() !== null) {
                        $buildRequestStep = $buildRequestStep->withImage(
                            $this->guessImageNameIfNeeded($buildRequestStep, $credentialsBucketUuid, $tideUuid)
                        );
                    }

                    return $buildRequestStep;
                }, $serviceConfiguration->getBuilderSteps()));
            }, $configuration->getServices())),
            $credentialsBucketUuid,
            $parentLog
        );
    }

    private function guessImageNameIfNeeded(BuildRequestStep $step, UuidInterface $bucketUuid, UuidInterface $tideUuid) : Image
    {
        $image = $step->getImage();
        $parts = explode('/', $image->getName());

        // Everything sounds good
        if (count($parts) == 3) {
            return $step->getImage();
        }

        try {
            $bucket = $this->bucketRepository->find($bucketUuid);
        } catch (BucketNotFound $e) {
            $this->logger->warning('Unable to guess the Docker image registry or username', [
                'exception' => $e,
                'tide_uuid' => $tideUuid->toString(),
            ]);

            return $image;
        }

        if (0 === $bucket->getDockerRegistries()->count()) {
            $this->logger->warning('No docker registries found in team to guess image registry or username', [
                'tide_uuid' => $tideUuid->toString(),
            ]);

            return $image;
        }

        /** @var DockerRegistry $registry */
        $registry = $bucket->getDockerRegistries()->first();

        // We will consider the username as missing
        if (count($parts) == 1) {
            array_unshift($parts, $registry->getUsername());
        }

        if (count($parts) == 2) {
            array_unshift($parts, $registry->getServerAddress());
        }

        return new Image(
            implode('/', $parts),
            $image->getTag()
        );
    }
}
