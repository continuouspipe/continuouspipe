<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class BuildRequestCreator
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
     * @param LoggerInterface            $logger
     * @param BuildRequestSourceResolver $buildRequestSourceResolver
     */
    public function __construct(LoggerInterface $logger, BuildRequestSourceResolver $buildRequestSourceResolver)
    {
        $this->logger = $logger;
        $this->buildRequestSourceResolver = $buildRequestSourceResolver;
    }

    /**
     * @param CodeReference          $codeReference
     * @param BuildTaskConfiguration $configuration
     * @param Uuid                   $credentialsBucketUuid
     *
     * @return Request\BuildRequest[]
     */
    public function createBuildRequests(CodeReference $codeReference, BuildTaskConfiguration $configuration, Uuid $credentialsBucketUuid)
    {
        $this->logger->info('Creating build requests', [
            'codeReference' => $codeReference,
            'configuration' => $configuration,
        ]);

        $buildRequests = [];
        foreach ($configuration->getServices() as $serviceName => $service) {
            $image = new Image($service->getImage(), $service->getTag());
            $buildRequests[] = new BuildRequest(
                $this->buildRequestSourceResolver->getSource($codeReference),
                $image,
                new Context(
                    $service->getDockerFilePath(),
                    $service->getBuildDirectory()
                ),
                null, null,
                $service->getEnvironment(),
                $credentialsBucketUuid
            );
        }

        return $buildRequests;
    }
}
