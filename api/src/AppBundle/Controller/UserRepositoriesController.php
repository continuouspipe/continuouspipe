<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.user_repositories")
 */
class UserRepositoriesController
{
    /**
     * @var CodeRepositoryRepository
     */
    private $codeRepositoryRepository;

    /**
     * @param CodeRepositoryRepository $codeRepositoryRepository
     */
    public function __construct(CodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->codeRepositoryRepository = $codeRepositoryRepository;
    }

    /**
     * @Route("/user-repositories")
     * @View
     */
    public function listAction()
    {
        return $this->codeRepositoryRepository->findByCurrentUser();
    }

    /**
     * @Route("/user-repositories/organization/{organization}")
     * @View
     */
    public function listByOrganizationAction($organization)
    {
        return $this->codeRepositoryRepository->findByOrganization($organization);
    }
}
