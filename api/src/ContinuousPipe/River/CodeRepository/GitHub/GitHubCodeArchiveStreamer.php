<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenException;
use GitHub\Integration\InstallationTokenResolver;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\UuidInterface;
use GuzzleHttp\Exception\RequestException;

class GitHubCodeArchiveStreamer implements CodeArchiveStreamer
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var InstallationRepository
     */
    private $gitHubInstallationRepository;

    /**
     * @var InstallationTokenResolver
     */
    private $gitHubInstallationTokenResolver;

    public function __construct(
        ClientInterface $httpClient,
        InstallationRepository $gitHubInstallationRepository,
        InstallationTokenResolver $gitHubInstallationTokenResolver
    ) {
        $this->httpClient = $httpClient;
        $this->gitHubInstallationRepository = $gitHubInstallationRepository;
        $this->gitHubInstallationTokenResolver = $gitHubInstallationTokenResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function streamCodeArchive(UuidInterface $flowUuid, CodeReference $codeReference): StreamInterface
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new \InvalidArgumentException('The repository for this flow is not supported');
        }

        try {
            $installation = $this->gitHubInstallationRepository->findByRepository($repository);
        } catch (InstallationNotFound $e) {
            throw new CodeRepositoryException('GitHub installation not found while creating a build', 404, $e);
        }

        try {
            $token = $this->gitHubInstallationTokenResolver->get($installation)->getToken();
        } catch (InstallationTokenException $e) {
            throw new CodeRepositoryException('Unable to get GitHub installation token', 500, $e);
        }

        try {
            $gitHubResponse = $this->httpClient->request(
                'GET',
                sprintf(
                    'https://api.github.com/repos/%s/%s/tarball/%s',
                    $repository->getOrganisation(),
                    $repository->getName(),
                    $codeReference->getCommitSha() ?: $codeReference->getBranch()
                ),
                [
                    'stream' => true,
                    'headers' => [
                        'Authorization' => 'token '.$token,
                    ],
                ]
            );
        } catch (RequestException $e) {
            throw new CodeRepositoryException('Unable to download the source code from the code repository', 500, $e);
        }

        return $gitHubResponse->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof GitHubCodeRepository;
    }
}
