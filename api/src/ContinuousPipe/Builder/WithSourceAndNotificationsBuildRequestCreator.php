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
     * {@inheritdoc}
     */
    public function createBuildRequests(UuidInterface $flowUuid, UuidInterface $tideUuid, CodeReference $codeReference, BuildTaskConfiguration $configuration, UuidInterface $credentialsBucketUuid, Log $parentLog) : array
    {
        $this->logger->info('Creating build requests', [
            'codeReference' => $codeReference,
            'configuration' => $configuration,
        ]);

        $address = 'http'.($this->useSsl ? 's' : '').'://'.$this->riverHostname.$this->urlGenerator->generate('builder_notification_post', [
            'tideUuid' => (string) $tideUuid,
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        $codeBaseSource = $this->buildRequestSourceResolver->getSource($flowUuid, $codeReference);
        $buildRequests = array_map(function (ServiceConfiguration $serviceConfiguration) use ($codeBaseSource, $address, $parentLog, $credentialsBucketUuid) {
            return new BuildRequest(
                array_map(function (BuildStepConfiguration $step) use ($codeBaseSource) {
                    return $step->withSource($codeBaseSource);
                }, $serviceConfiguration->getBuilderSteps()),
                Notification::withHttp(HttpNotification::fromAddress($address)),
                Logging::withLogStream(LogStreamLogging::fromParentLogIdentifier($parentLog->getId())),
                $credentialsBucketUuid
            );
        }, $configuration->getServices());

        return $buildRequests;
    }
}
