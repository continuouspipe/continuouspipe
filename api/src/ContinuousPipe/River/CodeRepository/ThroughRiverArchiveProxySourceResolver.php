<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\Builder\BuildRequestSourceResolver;
use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\User;

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
     * @var JWTManagerInterface
     */
    private $jwtManager;

    /**
     * @var string
     */
    private $riverUrl;

    public function __construct(LoggerInterface $logger, UrlGeneratorInterface $urlGenerator, JWTManagerInterface $jwtManager, string $riverUrl)
    {
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->jwtManager = $jwtManager;
        $this->riverUrl = $riverUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $token = $this->jwtManager->create(new User(
            'continuouspipe_builder_for_sources',
            null
        ));

        return new Archive(
            'https://'.$this->riverUrl.$this->urlGenerator->generate('flow_source_code_archive', [
                'flowUuid' => $flowUuid->toString(),
                'reference' => $codeReference->getCommitSha() ?: $codeReference->getBranch(),
            ]), [
                'Authorization' => 'Bearer '.$token
            ]
        );
    }
}
