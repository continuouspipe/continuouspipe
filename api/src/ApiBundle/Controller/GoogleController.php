<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Google\ProjectRepository;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\GoogleAccount;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/accounts/{uuid}/google", service="api.controller.google")
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
     * @ParamConverter("account", converter="account")
     * @View
     */
    public function listProjectsAction(Account $account)
    {
        if (!$account instanceof GoogleAccount) {
            return new Response(null, Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->projectRepository->findAll($account);
    }
}
