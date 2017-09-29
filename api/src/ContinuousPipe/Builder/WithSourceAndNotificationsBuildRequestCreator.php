<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
use LogStream\Log;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WithSourceAndNotificationsBuildRequestCreator implements BuildRequestCreator
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var BuildRequestSourceResolver
     */
    private $buildRequestSourceResolver;

    /**
     * @param LoggerInterface $logger
     * @param BuildRequestSourceResolver $buildRequestSourceResolver
     */
    public function __construct(LoggerInterface $logger, BuildRequestSourceResolver $buildRequestSourceResolver)
    {
        $this->logger = $logger;
        $this->buildRequestSourceResolver = $buildRequestSourceResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuildRequests(UuidInterface $flowUuid, UuidInterface $tideUuid, CodeReference $codeReference, BuildTaskConfiguration $configuration, UuidInterface $credentialsBucketUuid, Log $parentLog) : array
    {
        $this->logger->info('Creating build requests', [
            'codeReference' => $codeReference,
            'configuration' => $configuration,
        ]);

        $codeBaseSource = $this->buildRequestSourceResolver->getSource($flowUuid, $codeReference);
        $buildRequests = array_map(function (ServiceConfiguration $serviceConfiguration) use ($codeBaseSource, $parentLog, $credentialsBucketUuid) {
            return new BuildRequest(
                array_map(function (BuildStepConfiguration $step) use ($codeBaseSource) {
                    return $step->withSource($codeBaseSource);
                }, $serviceConfiguration->getBuilderSteps()),
                Logging::withLogStream(LogStreamLogging::fromParentLogIdentifier($parentLog->getId())),
                $credentialsBucketUuid
            );
        }, $configuration->getServices());

        return $buildRequests;
    }
}
