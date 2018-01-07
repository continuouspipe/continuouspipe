<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use Adlogix\GuzzleAtlassianConnect\Security\HeaderAuthentication;
use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\InstallationRepository;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class BitBucketCodeArchiveStreamer implements CodeArchiveStreamer
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientInterface $httpClient
     * @param InstallationRepository $installationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $httpClient,
        InstallationRepository $installationRepository,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->installationRepository = $installationRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function streamCodeArchive(UuidInterface $flowUuid, CodeReference $codeReference): StreamInterface
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new \InvalidArgumentException('This build request source resolver only supports BitBucket repositories');
        }

        $uri = sprintf(
            '%s/get/%s.tar.gz',
            $repository->getApiSlug(),
            $codeReference->getCommitSha() ?: $codeReference->getBranch()
        );

        $installation = $this->findInstallation($repository);
        $authentication = new HeaderAuthentication($installation->getKey(), $installation->getSharedSecret());
        $authentication->getTokenInstance()->setSubject($installation->getClientKey());
        $authentication->setQueryString('GET', $uri);

        try {
            $gitHubResponse = $this->httpClient->request(
                'GET',
                'https://bitbucket.org/'.$uri,
                [
                    'stream' => true,
                    'headers' => $authentication->getHeaders(),
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
        return $codeReference->getRepository() instanceof BitBucketCodeRepository;
    }

    /**
     * @param BitBucketCodeRepository $repository
     *
     * @throws CodeRepositoryException
     *
     * @return Installation
     */
    private function findInstallation(BitBucketCodeRepository $repository): Installation
    {
        $installations = $this->installationRepository->findByPrincipal(
            $repository->getOwner()->getType(),
            $repository->getOwner()->getUsername()
        );

        if (count($installations) == 0) {
            throw new CodeRepositoryException('BitBucket add-on installation not found for this repository');
        } elseif (count($installations) > 1) {
            $this->logger->alert('Found multiple installations for a given code repository', [
                'repository_owner' => $repository->getOwner()->getUsername(),
                'repository_name' => $repository->getName(),
            ]);
        }

        return current($installations);
    }
}
