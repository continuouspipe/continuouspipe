<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Builder;

use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenResolver;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GitHubBuildRequestSourceResolver implements CodeRepository\ImplementationDelegation\BuildRequestSourceResolverAdapter
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var string
     */
    private $riverUrl;

    public function __construct(
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        string $riverUrl
    ) {
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->riverUrl = $riverUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        return new Archive(
            'https://'.$this->riverUrl.$this->urlGenerator->generate('flow_source_code_archive', [
                'flowUuid' => $flowUuid->toString(),
                'reference' => $codeReference->getCommitSha() ?: $codeReference->getBranch(),
            ])
        );
    }

    public function supports(UuidInterface $flowUuid, CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof CodeRepository\GitHub\GitHubCodeRepository;
    }
}
