<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use LogStream\Log;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var string
     */
    private $riverHostname;
    /**
     * @var bool
     */
    private $useSsl;

    /**
     * @param LoggerInterface $logger
     * @param BuildRequestSourceResolver $buildRequestSourceResolver
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $riverHostname
     * @param bool $useSsl
     */
    public function __construct(LoggerInterface $logger, BuildRequestSourceResolver $buildRequestSourceResolver, UrlGeneratorInterface $urlGenerator, string $riverHostname, bool $useSsl)
    {
        $this->logger = $logger;
        $this->buildRequestSourceResolver = $buildRequestSourceResolver;
        $this->urlGenerator = $urlGenerator;
        $this->riverHostname = $riverHostname;
        $this->useSsl = $useSsl;
    }

    /**
     * @param CodeReference $codeReference
     * @param BuildTaskConfiguration $configuration
     * @param Uuid $credentialsBucketUuid
     * @param Log $parentLog
     *
     * @return BuildRequest[]
     */
    public function createBuildRequests(UuidInterface $tideUuid, CodeReference $codeReference, BuildTaskConfiguration $configuration, Uuid $credentialsBucketUuid, Log $parentLog)
    {
        $this->logger->info('Creating build requests', [
            'codeReference' => $codeReference,
            'configuration' => $configuration,
        ]);

        $address = 'http'.($this->useSsl ? 's' : '').'://'.$this->riverHostname.$this->urlGenerator->generate('builder_notification_post', [
            'tideUuid' => (string) $tideUuid,
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

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
                Notification::withHttp(HttpNotification::fromAddress($address)),
                Logging::withLogStream(LogStreamLogging::fromParentLogIdentifier($parentLog->getId())),
                $service->getEnvironment(),
                $credentialsBucketUuid
            );
        }

        return $buildRequests;
    }
}
