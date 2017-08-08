<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route(service="api.controller.kube_status_proxy")
 */
class KubeStatusProxyController
{
    /**
     * @var ClientInterface
     */
    private $kubeStatusHttpClient;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(
        ClientInterface $kubeStatusHttpClient,
        TeamRepository $teamRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->kubeStatusHttpClient = $kubeStatusHttpClient;
        $this->authorizationChecker = $authorizationChecker;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @Route("/kube-status/clusters/{clusterIdentifier}/history")
     * @View
     */
    public function clusterHistoryAction(string $clusterIdentifier)
    {
        $teamName = substr($clusterIdentifier, 0, strpos($clusterIdentifier, '+'));

        try {
            $team = $this->teamRepository->find($teamName);
        } catch (TeamNotFound $e) {
            throw new NotFoundHttpException(sprintf('Team "%s" is not found', $teamName));
        }

        if (!$this->authorizationChecker->isGranted('READ', $team)) {
            throw new AccessDeniedHttpException(sprintf('You do not have access to the team "%s"', $teamName));
        }

        try {
            $response = $this->kubeStatusHttpClient->request(
                'GET',
                '/clusters/' . $clusterIdentifier . '/history'
            );
        } catch (RequestException $e) {
            if (null === ($response = $e->getResponse())) {
                return new JsonResponse(['error' => $e->getMessage()], 500);
            }
        }

        return new Response($response->getBody()->getContents(), $response->getStatusCode(), [
            'Content-Type' => $response->getHeaderLine('Content-Type'),
        ]);
    }
}
