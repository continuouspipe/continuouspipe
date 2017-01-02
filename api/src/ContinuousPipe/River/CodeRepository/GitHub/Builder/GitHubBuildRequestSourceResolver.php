<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Builder;

use ContinuousPipe\Builder\BuildRequestSourceResolver;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenResolver;
use Psr\Log\LoggerInterface;

class GitHubBuildRequestSourceResolver implements CodeRepository\ImplementationDelegation\BuildRequestSourceResolverAdapter
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
     * {@inheritdoc}
     */
    public function getSource(CodeReference $codeReference)
    {
        return new Repository(
            $codeReference->getRepository()->getAddress(),
            $codeReference->getCommitSha(),
            $this->getTokenFromRepository($codeReference->getRepository())
        );
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
            $installation = $this->installationRepository->findByRepository($codeRepository);
        } catch (InstallationNotFound $e) {
            $this->logger->warning('GitHub installation not found while creating a build: {message}', [
                'repository_identifier' => $codeRepository->getIdentifier(),
                'repository_type' => $codeRepository->getType(),
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $this->installationTokenResolver->get($installation)->getToken();
    }

    public function supports(CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof CodeRepository\GitHub\GitHubCodeRepository;
    }
}
