<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Builder;

use Adlogix\GuzzleAtlassianConnect\Security\HeaderAuthentication;
use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\InstallationRepository;
use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\ImplementationDelegation\BuildRequestSourceResolverAdapter;
use Psr\Log\LoggerInterface;

class BitBucketBuildRequestSourceResolver implements BuildRequestSourceResolverAdapter
{
    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BitBucketBuildRequestSourceResolver constructor.
     *
     * @param InstallationRepository $installationRepository
     */
    public function __construct(InstallationRepository $installationRepository, LoggerInterface $logger)
    {
        $this->installationRepository = $installationRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(CodeReference $codeReference)
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new BuilderException('This build request source resolver only supports BitBucket repositories');
        }

        $uri = sprintf(
            '/%s/%s/get/%s.tar.gz',
            $repository->getOwner()->getUsername(),
            $repository->getName(),
            $codeReference->getCommitSha() ?: $codeReference->getBranch()
        );

        $installation = $this->findInstalation($repository);
        $authentication = new HeaderAuthentication($installation->getKey(), $installation->getSharedSecret());
        $authentication->getTokenInstance()->setSubject($installation->getClientKey());
        $authentication->setQueryString('GET', $uri);

        return new Archive(
            'https://bitbucket.org'.$uri,
            $authentication->getHeaders()
        );
    }

    public function supports(CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof BitBucketCodeRepository;
    }

    /**
     * @param BitBucketCodeRepository $repository
     *
     * @throws BuilderException
     *
     * @return Installation
     */
    private function findInstalation(BitBucketCodeRepository $repository): Installation
    {
        $installations = $this->installationRepository->findByPrincipal(
            $repository->getOwner()->getType(),
            $repository->getOwner()->getUsername()
        );

        if (count($installations) == 0) {
            throw new BuilderException('BitBucket add-on installation not found for this repository');
        } elseif (count($installations) > 1) {
            $this->logger->alert('Found multiple installations for a given code repository', [
                'repository_owner' => $repository->getOwner()->getUsername(),
                'repository_name' => $repository->getName(),
            ]);
        }

        return current($installations);
    }
}
