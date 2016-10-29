<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenResolver;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class BuildRequestCreator
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @var InstallationTokenResolver
     */
    private $installationTokenResolver;

    /**
     * @param LoggerInterface           $logger
     * @param InstallationRepository    $installationRepository
     * @param InstallationTokenResolver $installationTokenResolver
     */
    public function __construct(LoggerInterface $logger, InstallationRepository $installationRepository, InstallationTokenResolver $installationTokenResolver)
    {
        $this->logger = $logger;
        $this->installationRepository = $installationRepository;
        $this->installationTokenResolver = $installationTokenResolver;
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
            $buildRequestRepository = new Repository(
                $codeReference->getRepository()->getAddress(),
                $codeReference->getCommitSha(),
                $this->getTokenFromRepository($codeReference->getRepository())
            );
            $buildRequests[] = new BuildRequest(
                $buildRequestRepository,
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

    /**
     * @param CodeRepository $codeRepository
     *
     * @return string|null
     */
    private function getTokenFromRepository(CodeRepository $codeRepository)
    {
        if (!$codeRepository instanceof CodeRepository\GitHub\GitHubCodeRepository) {
            return null;
        }

        try {
            $installation = $this->installationRepository->findByAccount(
                $codeRepository->getGitHubRepository()->getOwner()->getLogin()
            );
        } catch (InstallationNotFound $e) {
            $this->logger->warning('GitHub installation not found while creating a build', [
                'repository_identifier' => $codeRepository->getIdentifier(),
                'repository_type' => $codeRepository->getType(),
            ]);

            return null;
        }

        return $this->installationTokenResolver->get($installation)->getToken();
    }
}
