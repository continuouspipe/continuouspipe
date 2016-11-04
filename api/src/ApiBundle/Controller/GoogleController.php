<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Google\ProjectRepository;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\GoogleAccount;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/accounts/{uuid}/google", service="api.controller.google")
 * @ParamConverter("account", converter="account")
 * @Security("is_granted('ACCESS', account)")
 */
class GoogleController
{
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @param ProjectRepository $projectRepository
     */
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @Route("/projects", methods={"GET"})
     * @View
     */
    public function listProjectsAction(Account $account)
    {
        if (!$account instanceof GoogleAccount) {
            return new JsonResponse([
                'error' => 'The account is not a Google account',
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            return $this->projectRepository->findAll($account);
        } catch (GoogleException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}
