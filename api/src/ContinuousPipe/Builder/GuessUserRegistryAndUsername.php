<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Managed\Resources\DockerRegistry\ReferenceRegistryResolver;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
use ContinuousPipe\Security\Credentials\BucketRepository;
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
    /**
     * @var ReferenceRegistryResolver
     */
    private $referenceRegistryResolver;

    public function __construct(
        BuildRequestCreator $decoratedCreator,
        BucketRepository $bucketRepository,
        LoggerInterface $logger,
        ReferenceRegistryResolver $referenceRegistryResolver
    ) {
        $this->decoratedCreator = $decoratedCreator;
        $this->bucketRepository = $bucketRepository;
        $this->logger = $logger;
        $this->referenceRegistryResolver = $referenceRegistryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuildRequests(
        UuidInterface $flowUuid,
        UuidInterface $tideUuid,
        CodeReference $codeReference,
        BuildTaskConfiguration $configuration,
        UuidInterface $credentialsBucketUuid,
        Log $parentLog
    ): array {
        return $this->decoratedCreator->createBuildRequests(
            $flowUuid,
            $tideUuid,
            $codeReference,
            new BuildTaskConfiguration(array_map(function (ServiceConfiguration $serviceConfiguration) use ($tideUuid, $flowUuid, $credentialsBucketUuid) {
                return new ServiceConfiguration(array_map(function (BuildStepConfiguration $buildRequestStep) use ($tideUuid, $flowUuid, $credentialsBucketUuid) {
                    if ($buildRequestStep->getImage() !== null) {
                        $buildRequestStep = $buildRequestStep->withImage(
                            $this->guessImageNameIfNeeded($buildRequestStep, $credentialsBucketUuid, $tideUuid, $flowUuid)
                        );
                    }

                    return $buildRequestStep;
                }, $serviceConfiguration->getBuilderSteps()));
            }, $configuration->getServices())),
            $credentialsBucketUuid,
            $parentLog
        );
    }

    private function guessImageNameIfNeeded(BuildStepConfiguration $step, UuidInterface $bucketUuid, UuidInterface $tideUuid, UuidInterface $flowUuid) : Image
    {
        $image = $step->getImage();
        $parts = explode('/', $image->getName());

        // Everything sounds good
        if (count($parts) == 3) {
            return $step->getImage();
        }

        // Get registry of reference
        if (null === ($registry = $this->referenceRegistryResolver->getReferenceRegistry($flowUuid))) {
            if (empty($image->getName())) {
                throw new BuilderException(sprintf(
                    'Docker image name to build "%s" is invalid.',
                    $image->getName()
                ));
            }

            $this->logger->warning('Can\'t find any reference registry for this tide', [
                'tide_uuid' => $tideUuid->toString(),
            ]);

            return $image;
        }

        if (empty($image->getName()) && null !== ($fullAddress = $registry->getFullAddress())) {
            $imageName = $registry->getFullAddress();
        } else {
            if (count($parts) == 1) {
                array_unshift($parts, $registry->getUsername());
            }

            if (count($parts) == 2) {
                array_unshift($parts, $registry->getServerAddress());
            }

            $imageName = implode('/', $parts);
        }

        return new Image(
            $imageName,
            $image->getTag(),
            $image->getReuse()
        );
    }
}
