<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\Builder\BuildRequestSourceResolver;
use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ThroughRiverArchiveProxySourceResolver implements BuildRequestSourceResolver
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

    public function __construct(LoggerInterface $logger, UrlGeneratorInterface $urlGenerator, string $riverUrl)
    {
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
}
