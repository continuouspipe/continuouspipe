<?php

namespace GitHub\Integration;

use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\CodeRepository\Commit;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ApiBranchQuery implements BranchQuery
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;
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
        InstallationTokenResolver $gitHubInstallationTokenResolver,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->gitHubInstallationRepository = $gitHubInstallationRepository;
        $this->gitHubInstallationTokenResolver = $gitHubInstallationTokenResolver;
    }

    /**
     * @return Branch[]
     */
    public function findBranches(FlatFlow $flow): array
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new \InvalidArgumentException('The repository of this flow is not supported');
        }

        return array_map(
            function (array $b) use ($repository) {
                $branch = Branch::github($b['name'], $repository->getAddress());

                if (isset($b['commit']['sha']) && isset($b['commit']['url'])) {
                    return $branch->withLatestCommit(Commit::fromShaAndGitubApiUrl($b['commit']['sha'], $b['commit']['url']));
                }

                return $branch;
            },
            $this->fetchBranches($this->getToken($repository), $this->branchUri($repository))
        );
    }

    private function getToken($repository)
    {
        $installation = $this->gitHubInstallationRepository->findByRepository($repository);
        return $this->gitHubInstallationTokenResolver->get($installation)->getToken();
    }

    private function fetchBranches($token, $link)
    {
        $response = $this->httpClient->request(
            'GET',
            $link,
            [
                'stream' => true,
                'headers' => [
                    'Authorization' => 'token ' . $token,
                ],
            ]
        );

        $body = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        if (null !== $nextLink = $this->nextLink($response)) {
            return array_merge($body, $this->fetchBranches($token, $nextLink));
        }

        return $body;
    }

    /**
     * @param $repository
     * @return string
     */
    private function branchUri($repository)
    {
        return sprintf(
            'https://api.github.com/repos/%s/%s/branches',
            $repository->getOrganisation(),
            $repository->getName()
        );
    }

    private function nextLink(ResponseInterface $response)
    {
        if (!$response->hasHeader('Link')) {
            return;
        }

        $nextLinks = array_filter(
            Psr7\parse_header($response->getHeader('Link')),
            function (array $link) {
                return isset($link['rel']) && $link['rel'] == 'next';
            }
        );

        if (count($nextLinks) == 0) {
            return;
        }

        $nextLink = array_shift($nextLinks);

        return trim($nextLink[0], '<>');
    }

}