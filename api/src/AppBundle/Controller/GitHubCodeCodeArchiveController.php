<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\Builder\GitHubBuildRequestSourceResolver;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenException;
use GitHub\Integration\InstallationTokenResolver;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.github_code_archive")
 */
class GitHubCodeCodeArchiveController
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
     * @Route("/github/flows/{flowUuid}/source-code/archive/{reference}", methods={"GET"}, name="flow_source_code_archive")
     * @ParamConverter("flow", converter="flow", options={"identifier"="flowUuid", "flat"=true})
     * @View
     */
    public function flowArchiveProxyAction(FlatFlow $flow, string $reference)
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new BadRequestHttpException('The repository for this flow is not supported');
        }

        try {
            $installation = $this->gitHubInstallationRepository->findByRepository($repository);
        } catch (InstallationNotFound $e) {
            $this->logger->warning('GitHub installation not found while creating a build: {message}', [
                'flow_uuid' => $flow->getUuid()->toString(),
                'exception' => $e,
            ]);

            return new JsonResponse([
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ], 404);
        }

        try {
            $token = $this->gitHubInstallationTokenResolver->get($installation)->getToken();
        } catch (InstallationTokenException $e) {
            $this->logger->warning('Unable to get GitHub installation token', [
                'flow_uuid' => $flow->getUuid()->toString(),
                'exception' => $e,
            ]);

            return new JsonResponse([
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ], 404);
        }

        try {
            $gitHubResponse = $this->httpClient->request(
                'GET',
                sprintf(
                    'https://api.github.com/repos/%s/%s/tarball/%s',
                    $repository->getOrganisation(),
                    $repository->getName(),
                    $reference
                ),
                [
                    'stream' => true,
                    'headers' => [
                        'Authorization' => 'token ' . $token,
                    ],
                ]
            );
        } catch (RequestException $e) {
            $this->logger->warning('Unable to download the source code from the code repository', [
                'exception' => $e,
                'flow_uuid' => $flow->getUuid()->toString(),
            ]);

            return new Response(
                $e->getResponse()->getBody()->getContents(),
                $e->getResponse()->getStatusCode()
            );
        }

        $body = $gitHubResponse->getBody();
        $response = new StreamedResponse(function () use ($body) {
            while (!$body->eof()) {
                echo $body->read(1024);
            }
        });

        $response->headers->set('Content-Type', $gitHubResponse->getHeaderLine('Content-Type'));

        return $response;
    }
}
